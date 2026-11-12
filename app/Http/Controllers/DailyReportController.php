<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use App\Models\DailyReportPhoto;
use App\Models\Project;
use App\Models\User;
use App\Services\PhotoStore;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Daily site reports on the website. The office reads what site staff filed
 * from the app; site staff see their own.
 */
class DailyReportController extends Controller
{
    public function index(Request $request): View|StreamedResponse
    {
        $this->authorize('viewAny', DailyReport::class);

        $filters = $this->filters($request);
        $query = $this->filteredQuery($request, $filters);

        if ($request->query('export') === 'csv') {
            return $this->exportCsv($query);
        }

        $reports = (clone $query)
            ->with(['project', 'user', 'photos'])
            ->orderByDesc('report_date')
            ->orderBy('project_id')
            ->paginate(20)
            ->withQueryString();

        $canViewAll = $request->user()->can('viewAll', DailyReport::class);

        return view('daily-reports.index', [
            'reports' => $reports,
            'filters' => $filters,
            'projects' => Project::query()->orderBy('name')->get(['id', 'name']),
            'reporters' => $canViewAll
                ? User::query()->where('company_id', $request->user()->company_id)->orderBy('name')->get(['id', 'name'])
                : collect(),
            'canViewAll' => $canViewAll,
            'totals' => [
                'reports' => (clone $query)->count(),
                'labour_days' => (int) (clone $query)->sum('labour_count'),
            ],
        ]);
    }

    public function show(DailyReport $report): View
    {
        $this->authorize('view', $report);

        $report->load(['project', 'user', 'photos.user']);

        return view('daily-reports.show', ['report' => $report]);
    }

    public function photo(Request $request, DailyReportPhoto $photo): StreamedResponse
    {
        $this->authorize('view', $photo->report);

        return Storage::disk(PhotoStore::DISK)->response(
            $request->query('size') === 'thumb' ? $photo->thumb_path : $photo->path,
            headers: ['Cache-Control' => 'private, max-age=86400'],
        );
    }

    public function destroyPhoto(Request $request, DailyReportPhoto $photo): RedirectResponse
    {
        abort_unless($photo->canBeDeletedBy($request->user()), 403);

        $report = $photo->report;
        $photo->delete();

        return redirect()->route('daily-reports.show', $report)->with('status', 'Photo deleted.');
    }

    /**
     * @return array{project: ?int, user: ?int, from: ?string, to: ?string}
     */
    private function filters(Request $request): array
    {
        $date = function (string $key) use ($request): ?string {
            $value = (string) $request->query($key, '');

            return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1 ? $value : null;
        };

        return [
            'project' => $request->integer('project') ?: null,
            'user' => $request->integer('user') ?: null,
            'from' => $date('from'),
            'to' => $date('to'),
        ];
    }

    /**
     * @param  array{project: ?int, user: ?int, from: ?string, to: ?string}  $filters
     * @return Builder<DailyReport>
     */
    private function filteredQuery(Request $request, array $filters): Builder
    {
        $canViewAll = $request->user()->can('viewAll', DailyReport::class);

        return DailyReport::query()
            ->when(! $canViewAll, fn ($q) => $q->where('user_id', $request->user()->id))
            ->when($canViewAll && $filters['user'], fn ($q) => $q->where('user_id', $filters['user']))
            ->when($filters['project'], fn ($q) => $q->where('project_id', $filters['project']))
            ->when($filters['from'], fn ($q) => $q->whereDate('report_date', '>=', $filters['from']))
            ->when($filters['to'], fn ($q) => $q->whereDate('report_date', '<=', $filters['to']));
    }

    /**
     * @param  Builder<DailyReport>  $query
     */
    private function exportCsv(Builder $query): StreamedResponse
    {
        $rows = $query->with(['project', 'user'])->withCount('photos')->orderBy('report_date')->orderBy('project_id')->lazy(500);

        return response()->streamDownload(function () use ($rows): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Date', 'Project', 'Reported by', 'Labour', 'Labour by trade', 'Work done', 'Notes', 'Photos']);

            foreach ($rows as $report) {
                $trades = collect($report->labour_breakdown ?? [])->map(fn ($row) => $row['trade'].': '.$row['count'])->implode(', ');

                fputcsv($out, array_map($this->csvSafe(...), [
                    $report->report_date?->format('Y-m-d'),
                    $report->project?->name,
                    $report->user?->name,
                    $report->labour_count,
                    $trades,
                    $report->work_done,
                    $report->notes,
                    $report->photos_count,
                ]));
            }

            fclose($out);
        }, 'daily-reports-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function csvSafe(mixed $value): mixed
    {
        return is_string($value) && preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
    }
}
