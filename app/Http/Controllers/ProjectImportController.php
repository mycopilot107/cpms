<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectImportUploadRequest;
use App\Models\Project;
use App\Services\ProjectSheetParser;
use App\Support\SpreadsheetReader;
use App\Support\SpreadsheetWriter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;

/**
 * Import projects from an Excel or CSV file: upload, check the preview, then
 * confirm. Nothing is saved until the user confirms. Rows with errors are skipped.
 */
class ProjectImportController extends Controller
{
    private const STORAGE_DIR = 'project-imports';

    private const TTL_MINUTES = 120;

    public function __construct(
        private readonly SpreadsheetReader $reader,
        private readonly SpreadsheetWriter $writer,
        private readonly ProjectSheetParser $parser,
    ) {}

    public function create(): View
    {
        $this->authorize('create', Project::class);

        return view('projects.import');
    }

    /** A filled .xlsx with the column names the import recognises. */
    public function template(): Response
    {
        $this->authorize('create', Project::class);

        return response($this->writer->xlsx(ProjectSheetParser::SAMPLE_ROWS, 'Projects'), 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="project-import-sample.xlsx"',
            'Cache-Control' => 'no-cache',
        ]);
    }

    /** Keep the file briefly, parse it, and send the user to the preview. */
    public function store(ProjectImportUploadRequest $request): RedirectResponse
    {
        $this->pruneOldFiles();

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $token = Str::random(32);
        $path = $file->storeAs(self::STORAGE_DIR, $token.'.'.$extension, 'local');

        try {
            $rows = $this->reader->rows(Storage::disk('local')->path($path), $extension);
            $result = $this->parser->parse($rows, $this->existingCodes());
        } catch (RuntimeException $e) {
            Storage::disk('local')->delete($path);

            return back()->withInput()->withErrors(['file' => $e->getMessage()]);
        }

        Storage::disk('local')->delete($path);

        Cache::put($this->cacheKey($request, $token), [
            'file_name' => $file->getClientOriginalName(),
            'lines' => $result['lines'],
            'summary' => $result['summary'],
        ], now()->addMinutes(self::TTL_MINUTES));

        return redirect()->route('projects.import.preview', $token);
    }

    public function preview(Request $request, string $token): View|RedirectResponse
    {
        $this->authorize('create', Project::class);

        $upload = Cache::get($this->cacheKey($request, $token));
        if (! is_array($upload)) {
            return $this->expired();
        }

        return view('projects.import-preview', [
            'token' => $token,
            'upload' => $upload,
        ]);
    }

    public function import(Request $request, string $token): RedirectResponse
    {
        $this->authorize('create', Project::class);

        $upload = Cache::get($this->cacheKey($request, $token));
        if (! is_array($upload)) {
            return $this->expired();
        }

        $ready = array_values(array_filter(
            $upload['lines'],
            fn (array $line) => $line['status'] === ProjectSheetParser::STATUS_IMPORT && is_array($line['data']),
        ));

        if ($ready === []) {
            return redirect()
                ->route('projects.import.preview', $token)
                ->withErrors(['import' => 'There are no projects to import. Fix the rows marked with an error and upload the file again.']);
        }

        $userId = $request->user()->id;
        $created = DB::transaction(function () use ($ready, $userId): int {
            $codes = array_values(array_filter(array_map(
                fn (array $line) => $line['data']['project_code'] ?? null,
                $ready,
            )));
            $taken = $codes === []
                ? []
                : Project::query()->whereIn('project_code', $codes)->pluck('project_code')
                    ->map(fn ($code) => mb_strtolower((string) $code))
                    ->all();
            $taken = array_flip($taken);

            $created = 0;
            foreach ($ready as $line) {
                $code = $line['data']['project_code'] ?? null;
                $key = $code !== null ? mb_strtolower($code) : null;
                if ($key !== null && isset($taken[$key])) {
                    continue;
                }

                $project = new Project($line['data']);
                $project->created_by = $userId;
                $project->save();
                $created++;
                if ($key !== null) {
                    $taken[$key] = true;
                }
            }

            return $created;
        });

        Cache::forget($this->cacheKey($request, $token));

        $skipped = count($ready) - $created + (int) $upload['summary']['error'];

        return redirect()
            ->route('projects.index')
            ->with('status', $created.' project(s) imported.'
                .($skipped > 0 ? " {$skipped} row(s) were skipped." : ''));
    }

    /**
     * @return list<string>
     */
    private function existingCodes(): array
    {
        return Project::query()
            ->whereNotNull('project_code')
            ->pluck('project_code')
            ->all();
    }

    private function cacheKey(Request $request, string $token): string
    {
        return 'project-import:'.$request->user()->company_id.':'.$request->user()->id.':'.$token;
    }

    private function expired(): RedirectResponse
    {
        return redirect()->route('projects.import.create')
            ->withErrors(['file' => 'That upload has expired. Please upload the file again.']);
    }

    private function pruneOldFiles(): void
    {
        $disk = Storage::disk('local');
        foreach ($disk->files(self::STORAGE_DIR) as $file) {
            if ($disk->lastModified($file) < now()->subMinutes(self::TTL_MINUTES)->getTimestamp()) {
                $disk->delete($file);
            }
        }
    }
}
