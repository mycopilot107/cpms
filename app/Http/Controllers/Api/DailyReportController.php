<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveDailyReportRequest;
use App\Http\Resources\DailyReportPhotoResource;
use App\Http\Resources\DailyReportResource;
use App\Models\DailyReport;
use App\Models\DailyReportPhoto;
use App\Models\Project;
use App\Services\PhotoStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Daily site reports from the Android app: the caller's own reports, filing
 * today's report (saving again the same day updates it), and its photos.
 */
class DailyReportController extends Controller
{
    /** Open projects the caller can report on. */
    public function projects(): JsonResponse
    {
        $this->authorize('create', DailyReport::class);

        $projects = Project::query()
            ->whereNotIn('status', [Project::STATUS_COMPLETED, Project::STATUS_CANCELLED])
            ->orderBy('name')
            ->get(['id', 'name', 'project_code']);

        return response()->json([
            'data' => $projects->map(fn (Project $p) => ['id' => $p->id, 'name' => $p->name, 'code' => $p->project_code]),
            'trades' => DailyReport::TRADES,
        ]);
    }

    /** The caller's own reports, newest first (?project_id=&date= to find one). */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', DailyReport::class);

        $reports = DailyReport::query()
            ->with(['project', 'user', 'photos.user'])
            ->where('user_id', $request->user()->id)
            ->when($request->integer('project_id'), fn ($q, $id) => $q->where('project_id', $id))
            ->when(
                preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $request->query('date')) === 1,
                fn ($q) => $q->whereDate('report_date', $request->query('date')),
            )
            ->orderByDesc('report_date')
            ->orderByDesc('id')
            ->limit(30)
            ->get();

        return DailyReportResource::collection($reports);
    }

    /** File or update the caller's report for that project and day. */
    public function store(SaveDailyReportRequest $request): JsonResponse
    {
        $attributes = $request->reportAttributes();

        $report = DailyReport::query()
            ->where('project_id', $attributes['project_id'])
            ->where('user_id', $request->user()->id)
            ->whereDate('report_date', $attributes['report_date'])
            ->first();

        $created = $report === null;
        $report ??= tap(new DailyReport, fn (DailyReport $r) => $r->user_id = $request->user()->id);

        $report->fill($attributes)->save();
        $report->load(['project', 'user', 'photos.user']);

        return (new DailyReportResource($report))->response()->setStatusCode($created ? 201 : 200);
    }

    public function storePhoto(Request $request, DailyReport $report, PhotoStore $store): JsonResponse
    {
        $this->authorize('update', $report);

        $validated = $request->validate([
            'photo' => ['required', 'file', 'image', 'mimes:jpeg,jpg,png,webp', 'max:15360'],
            'caption' => ['nullable', 'string', 'max:255'],
        ]);

        $saved = $store->save($request->file('photo'), 'daily-report-photos/'.$report->company_id.'/'.$report->id);

        $photo = new DailyReportPhoto(['caption' => $validated['caption'] ?? null]);
        $photo->forceFill($saved + [
            'company_id' => $report->company_id,
            'daily_report_id' => $report->id,
            'user_id' => $request->user()->id,
        ])->save();

        return (new DailyReportPhotoResource($photo->load('user')))->response()->setStatusCode(201);
    }

    public function destroyPhoto(Request $request, DailyReportPhoto $photo): Response
    {
        abort_unless($photo->canBeDeletedBy($request->user()), 403);

        $photo->delete();

        return response()->noContent();
    }

    public function photoFile(Request $request, DailyReportPhoto $photo): StreamedResponse
    {
        $this->authorize('view', $photo->report);

        return Storage::disk(PhotoStore::DISK)->response(
            $request->query('size') === 'thumb' ? $photo->thumb_path : $photo->path,
            headers: ['Cache-Control' => 'private, max-age=86400'],
        );
    }
}
