<?php

namespace App\Http\Controllers;

use App\Http\Requests\BoqImportUploadRequest;
use App\Models\BoqItem;
use App\Models\Project;
use App\Services\BoqSheetParser;
use App\Support\Money;
use App\Support\SpreadsheetReader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Import a BOQ from an Excel (.xlsx) or CSV file in three steps: upload,
 * check the preview (columns and rows), then confirm. Nothing is saved until
 * the user confirms. Amounts are always recomputed as quantity × rate.
 */
class BoqImportController extends Controller
{
    private const STORAGE_DIR = 'boq-imports';

    private const TTL_MINUTES = 120;

    public function __construct(
        private readonly SpreadsheetReader $reader,
        private readonly BoqSheetParser $parser,
    ) {}

    public function create(Request $request): View
    {
        $this->authorize('create', BoqItem::class);

        return view('finance.boq.import', [
            'projects' => Project::orderBy('name')->get(),
            'selectedProjectId' => $request->integer('project') ?: null,
        ]);
    }

    /** Step 1: keep the file briefly and send the user to the preview. */
    public function store(BoqImportUploadRequest $request): RedirectResponse
    {
        $this->pruneOldFiles();

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $token = Str::random(32);
        $path = $file->storeAs(self::STORAGE_DIR, $token.'.'.$extension, 'local');

        try {
            $sheets = $this->reader->sheetNames(Storage::disk('local')->path($path), $extension);
        } catch (RuntimeException $e) {
            Storage::disk('local')->delete($path);

            return back()->withInput()->withErrors(['file' => $e->getMessage()]);
        }

        Cache::put($this->cacheKey($request, $token), [
            'project_id' => (int) $request->validated('project_id'),
            'path' => $path,
            'extension' => $extension,
            'file_name' => $file->getClientOriginalName(),
            'sheets' => $sheets,
        ], now()->addMinutes(self::TTL_MINUTES));

        return redirect()->route('boq-items.import.preview', $token);
    }

    /** Step 2: show what will be imported, with column choices the user can change. */
    public function preview(Request $request, string $token): View|RedirectResponse
    {
        $this->authorize('create', BoqItem::class);

        $upload = $this->upload($request, $token);
        if ($upload === null) {
            return $this->expired();
        }

        $state = $this->state($request, $upload);
        if ($state instanceof RedirectResponse) {
            return $state;
        }

        return view('finance.boq.import-preview', $state + [
            'token' => $token,
            'upload' => $upload,
            'project' => Project::findOrFail($upload['project_id']),
            'fields' => [
                'description' => 'Description',
                'quantity' => 'Quantity',
                'rate' => 'Rate',
                'unit' => 'Unit',
                'item_code' => 'Item no. / code',
                'category' => 'Category',
                'amount' => 'Amount (for checking)',
            ],
            'existingCount' => BoqItem::query()->where('project_id', $upload['project_id'])->count(),
            'currencySymbol' => Money::symbol(),
        ]);
    }

    /** Step 3: save the importable lines. */
    public function import(Request $request, string $token): RedirectResponse
    {
        $this->authorize('create', BoqItem::class);

        $upload = $this->upload($request, $token);
        if ($upload === null) {
            return $this->expired();
        }

        $state = $this->state($request, $upload);
        if ($state instanceof RedirectResponse) {
            return $state;
        }

        $lines = array_values(array_filter(
            $state['result']['lines'],
            fn (array $line) => $line['status'] === BoqSheetParser::STATUS_IMPORT,
        ));

        if ($lines === []) {
            return redirect()
                ->route('boq-items.import.preview', ['token' => $token] + $request->query())
                ->withErrors(['import' => 'There are no lines to import. Check the column choices.']);
        }

        $projectId = $upload['project_id'];
        $replace = $request->boolean('replace_existing');
        $userId = $request->user()->id;

        $total = DB::transaction(function () use ($lines, $projectId, $replace, $userId): float {
            if ($replace) {
                BoqItem::query()->where('project_id', $projectId)->delete();
            }

            $sortOrder = (int) BoqItem::query()->where('project_id', $projectId)->max('sort_order');
            $total = 0.0;

            foreach ($lines as $line) {
                $data = $line['data'];
                $item = new BoqItem([
                    'project_id' => $projectId,
                    'item_code' => $data['item_code'] !== '' ? $data['item_code'] : null,
                    'category' => $data['category'] !== '' ? $data['category'] : null,
                    'description' => $data['description'],
                    'unit' => $data['unit'] !== '' ? $data['unit'] : null,
                    'quantity' => $data['quantity'],
                    'rate' => $data['rate'],
                    'sort_order' => ++$sortOrder,
                ]);
                $item->amount = round((float) $data['quantity'] * (float) $data['rate'], 2);
                $item->created_by = $userId;
                $item->save();

                $total += (float) $item->amount;
            }

            return $total;
        });

        Storage::disk('local')->delete($upload['path']);
        Cache::forget($this->cacheKey($request, $token));

        $skipped = $state['result']['summary']['error'];

        return redirect()
            ->route('boq-items.index', ['project' => $projectId])
            ->with('status', count($lines).' BOQ line(s) imported, total '.Money::format($total).'.'
                .($skipped > 0 ? " {$skipped} row(s) with errors were skipped." : ''));
    }

    /** A ready-to-fill CSV with the column names the import recognises. */
    public function template(): StreamedResponse
    {
        $this->authorize('create', BoqItem::class);

        return response()->streamDownload(function (): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Item No', 'Category', 'Description', 'Unit', 'Quantity', 'Rate', 'Amount']);
            fputcsv($out, ['1', 'Earthwork', 'Excavation in foundation trenches in ordinary soil, including disposal', 'Cum', '120', '350', '42000']);
            fputcsv($out, ['2', 'Concrete', 'PCC 1:4:8 in foundation bed', 'Cum', '18.5', '5200', '96200']);
            fputcsv($out, ['3', 'RCC', 'RCC M25 in columns and beams, excluding steel', 'Cum', '42', '7800', '327600']);
            fclose($out);
        }, 'boq-import-template.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Rows, header, mapping, and the parsed result for the current choices.
     *
     * @param  array{project_id: int, path: string, extension: string, file_name: string, sheets: list<string>}  $upload
     * @return array<string, mixed>|RedirectResponse
     */
    private function state(Request $request, array $upload): array|RedirectResponse
    {
        $sheet = in_array($request->input('sheet'), $upload['sheets'], true) ? (string) $request->input('sheet') : null;

        try {
            $rows = $this->reader->rows(Storage::disk('local')->path($upload['path']), $upload['extension'], $sheet);
        } catch (RuntimeException $e) {
            return redirect()->route('boq-items.import.create', ['project' => $upload['project_id']])
                ->withErrors(['file' => $e->getMessage()]);
        }

        if ($rows === []) {
            return redirect()->route('boq-items.import.create', ['project' => $upload['project_id']])
                ->withErrors(['file' => 'The sheet is empty.']);
        }

        $detectedHeader = $this->parser->detectHeaderRow($rows);
        $headerRow = $request->filled('header_row')
            ? max(0, min(count($rows) - 1, $request->integer('header_row') - 1))
            : ($detectedHeader ?? 0);

        $mapping = $this->parser->detectMapping($rows[$headerRow]);
        if ($request->has('map')) {
            $width = count($rows[0]);
            foreach (BoqSheetParser::FIELDS as $field) {
                $column = $request->input("map.{$field}");
                $mapping[$field] = is_numeric($column) && (int) $column >= 0 && (int) $column < $width ? (int) $column : null;
            }
        }

        $headingsAsCategory = $request->has('map') ? $request->boolean('headings') : true;

        $result = $mapping['description'] === null
            ? ['lines' => [], 'summary' => ['import' => 0, 'heading' => 0, 'skip' => 0, 'error' => 0, 'warnings' => 0, 'total' => 0.0]]
            : $this->parser->parse($rows, $headerRow, $mapping, $headingsAsCategory);

        return [
            'rows' => $rows,
            'sheet' => $sheet ?? ($upload['sheets'][0] ?? null),
            'headerRow' => $headerRow,
            'headerDetected' => $detectedHeader !== null,
            'mapping' => $mapping,
            'headingsAsCategory' => $headingsAsCategory,
            'columns' => $this->columnLabels($rows[$headerRow]),
            'result' => $result,
        ];
    }

    /**
     * "B — Description of item" labels for the column dropdowns.
     *
     * @param  list<string>  $header
     * @return array<int, string>
     */
    private function columnLabels(array $header): array
    {
        $labels = [];
        foreach ($header as $index => $text) {
            $letter = '';
            for ($n = $index + 1; $n > 0; $n = intdiv($n - 1, 26)) {
                $letter = chr(65 + ($n - 1) % 26).$letter;
            }
            $labels[$index] = $letter.($text !== '' ? ' — '.Str::limit($text, 40) : '');
        }

        return $labels;
    }

    /**
     * @return array{project_id: int, path: string, extension: string, file_name: string, sheets: list<string>}|null
     */
    private function upload(Request $request, string $token): ?array
    {
        $upload = Cache::get($this->cacheKey($request, $token));

        if (! is_array($upload) || ! Storage::disk('local')->exists($upload['path'])) {
            return null;
        }

        // The project must still belong to this company (the query is tenant-scoped).
        return Project::query()->whereKey($upload['project_id'])->exists() ? $upload : null;
    }

    private function cacheKey(Request $request, string $token): string
    {
        return 'boq-import:'.$request->user()->company_id.':'.$request->user()->id.':'.$token;
    }

    private function expired(): RedirectResponse
    {
        return redirect()->route('boq-items.import.create')
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
