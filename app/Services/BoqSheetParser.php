<?php

namespace App\Services;

/**
 * Turns the rows of a contractor's BOQ sheet into BOQ lines.
 *
 * Real bills rarely start on row 1: there are title rows, a header row with
 * names like "Sl. No. / Description of Item / Unit / Qty / Rate / Amount",
 * section rows such as "A. EARTHWORK", and total rows. This class finds the
 * header, guesses which column is which, and classifies every row so the user
 * can check the result before anything is saved.
 */
class BoqSheetParser
{
    public const FIELDS = ['item_code', 'category', 'description', 'unit', 'quantity', 'rate', 'amount'];

    public const STATUS_IMPORT = 'import';

    public const STATUS_HEADING = 'heading';

    public const STATUS_SKIP = 'skip';

    public const STATUS_ERROR = 'error';

    private const MAX_QUANTITY = 999_999_999_999.999;

    private const MAX_RATE = 9_999_999_999_999.99;

    private const LIMITS = ['item_code' => 50, 'category' => 100, 'unit' => 30, 'description' => 5000];

    /**
     * Header keywords per field, checked in this order so "Unit rate" maps to
     * rate rather than unit, and "Description of item" to description.
     *
     * @var array<string, list<string>>
     */
    private const SYNONYMS = [
        'description' => ['description', 'particular', 'specification', 'name of item', 'item of work', 'details of work', 'work description', 'nomenclature'],
        'rate' => ['unit rate', 'rate', 'unit price', 'price'],
        'quantity' => ['quantity', 'qty', 'quant', 'nos of units'],
        'amount' => ['amount', 'total', 'value', 'cost'],
        'unit' => ['unit', 'uom', 'units'],
        'item_code' => ['item no', 'item code', 'sl no', 'sr no', 's no', 'sno', 'serial', 'code', 'ref', 'item', 'no'],
        'category' => ['category', 'trade', 'section', 'head', 'group', 'package'],
    ];

    private const LUMP_SUM = ['ls', 'l s', 'lumpsum', 'lump sum', 'job', 'l/s', 'lot'];

    /** 0-based index of the most likely header row, or null if none looks like one. */
    public function detectHeaderRow(array $rows): ?int
    {
        foreach (array_slice($rows, 0, 40, true) as $index => $row) {
            $mapping = $this->detectMapping($row);
            $found = array_filter($mapping, fn ($column) => $column !== null);

            if (count($found) >= 2 && (isset($found['description']) || isset($found['quantity']))) {
                return $index;
            }
        }

        return null;
    }

    /**
     * Field => 0-based column index (or null) for a header row.
     *
     * @param  list<string>  $header
     * @return array<string, ?int>
     */
    public function detectMapping(array $header): array
    {
        $normalized = array_map(fn ($cell) => $this->normalizeHeader((string) $cell), $header);
        $mapping = array_fill_keys(self::FIELDS, null);
        $taken = [];

        foreach (self::SYNONYMS as $field => $keywords) {
            foreach ($normalized as $column => $text) {
                if ($text === '' || isset($taken[$column])) {
                    continue;
                }
                if ($this->headerMatches($field, $text, $keywords)) {
                    $mapping[$field] = $column;
                    $taken[$column] = true;
                    break;
                }
            }
        }

        return $mapping;
    }

    /**
     * Classify every row below the header.
     *
     * @param  list<list<string>>  $rows
     * @param  array<string, ?int>  $mapping
     * @return array{lines: list<array{row: int, status: string, data: array<string, mixed>, messages: list<string>}>, summary: array{import: int, heading: int, skip: int, error: int, warnings: int, total: float}}
     */
    public function parse(array $rows, int $headerRow, array $mapping, bool $headingsAsCategory = true): array
    {
        $lines = [];
        $currentHeading = null;
        $parentText = null;
        $parentCode = null;
        $summary = ['import' => 0, 'heading' => 0, 'skip' => 0, 'error' => 0, 'warnings' => 0, 'total' => 0.0];

        foreach ($rows as $index => $row) {
            if ($index <= $headerRow) {
                continue;
            }

            $raw = [];
            foreach (self::FIELDS as $field) {
                $column = $mapping[$field] ?? null;
                $raw[$field] = $column === null ? '' : $this->cleanText((string) ($row[$column] ?? ''));
            }

            foreach (['quantity', 'rate', 'amount'] as $numeric) {
                if (in_array(mb_strtolower($raw[$numeric]), ['-', '--', '—', 'nil', 'n/a'], true)) {
                    $raw[$numeric] = '';
                }
            }

            if (implode('', $raw) === '') {
                continue;
            }

            // Sub-items such as "(a) In ordinary soil" carry the quantities;
            // the long text above them says what the work is.
            $isSubItem = (bool) preg_match('/^\(?([a-z]|[ivx]{1,4}|\d{1,2})[\).]\s/i', $raw['description'], $marker);
            if ($isSubItem && $parentText !== null && ($raw['quantity'] !== '' || $raw['rate'] !== '')) {
                $raw['description'] = $parentText."\n".$raw['description'];
                if ($raw['item_code'] === '' && $parentCode !== null) {
                    $raw['item_code'] = $parentCode.'('.strtolower($marker[1]).')';
                }
            }

            $line = $this->classify($raw, $index + 1, $currentHeading, $headingsAsCategory);

            if ($line['status'] === self::STATUS_HEADING) {
                $currentHeading = $line['data']['category'];
                $parentText = $line['data']['description'];
                $parentCode = null;
            } elseif ($line['status'] === self::STATUS_SKIP && $raw['quantity'] === '' && $raw['rate'] === '' && $raw['description'] !== '' && ! $this->isTotalRow($raw['description'])) {
                $parentText = $raw['description'];
                $parentCode = $raw['item_code'] !== '' ? $raw['item_code'] : null;
                $line['messages'] = ['Description text — added to sub-items (a), (b)… below it.'];
            }

            $summary[$line['status']]++;
            if ($line['status'] === self::STATUS_IMPORT) {
                $summary['total'] += $line['data']['amount'];
                $summary['warnings'] += count($line['messages']) > 0 ? 1 : 0;
            }

            $lines[] = $line;
        }

        $summary['total'] = round($summary['total'], 2);

        return ['lines' => $lines, 'summary' => $summary];
    }

    /**
     * @param  array<string, string>  $raw
     * @return array{row: int, status: string, data: array<string, mixed>, messages: list<string>}
     */
    private function classify(array $raw, int $excelRow, ?string $currentHeading, bool $headingsAsCategory): array
    {
        $line = fn (string $status, array $data = [], array $messages = []) => [
            'row' => $excelRow,
            'status' => $status,
            'data' => $data,
            'messages' => $messages,
        ];

        $label = $raw['description'] !== '' ? $raw['description'] : $raw['item_code'];

        if ($this->isTotalRow($raw['description']) || $this->isTotalRow($raw['item_code'])) {
            return $line(self::STATUS_SKIP, ['description' => $label], ['Total row — not a BOQ line.']);
        }

        $hasQuantity = $raw['quantity'] !== '';
        $hasRate = $raw['rate'] !== '';

        if ($raw['description'] === '') {
            if ($hasQuantity || $hasRate) {
                return $line(self::STATUS_ERROR, ['description' => $raw['item_code']], ['Description is empty.']);
            }

            return $line(self::STATUS_SKIP, ['description' => $label], ['No description.']);
        }

        if (! $hasQuantity && ! $hasRate) {
            if ($headingsAsCategory && mb_strlen($raw['description']) <= self::LIMITS['category']) {
                return $line(self::STATUS_HEADING, ['category' => $raw['description'], 'description' => $raw['description']]);
            }

            return $line(self::STATUS_SKIP, ['description' => $raw['description']], ['Text only — no quantity or rate.']);
        }

        $messages = [];
        $errors = [];
        $unit = $raw['unit'];

        $quantity = $this->parseNumber($raw['quantity']);
        if (! $hasQuantity) {
            $quantity = 0.0;
            $messages[] = 'Quantity is empty — imported as 0.';
        } elseif ($quantity === null && in_array($this->normalizeHeader($raw['quantity']), self::LUMP_SUM, true)) {
            $quantity = 1.0;
            $unit = $unit !== '' ? $unit : 'LS';
            $messages[] = 'Lump sum — imported as quantity 1.';
        } elseif ($quantity === null) {
            $errors[] = 'Quantity "'.$raw['quantity'].'" is not a number.';
        }

        $rate = $this->parseNumber($raw['rate']);
        if (! $hasRate) {
            $rate = 0.0;
            $messages[] = 'Rate is empty — imported as 0.';
        } elseif ($rate === null) {
            $errors[] = 'Rate "'.$raw['rate'].'" is not a number.';
        }

        if ($quantity !== null && ($quantity < 0 || $quantity > self::MAX_QUANTITY)) {
            $errors[] = 'Quantity must be between 0 and '.number_format(self::MAX_QUANTITY).'.';
        }
        if ($rate !== null && ($rate < 0 || $rate > self::MAX_RATE)) {
            $errors[] = 'Rate must be between 0 and '.number_format(self::MAX_RATE).'.';
        }

        $data = [
            'item_code' => $raw['item_code'],
            'category' => $raw['category'] !== '' ? $raw['category'] : ($currentHeading ?? ''),
            'description' => $raw['description'],
            'unit' => $unit,
            'quantity' => $quantity === null ? null : round($quantity, 3),
            'rate' => $rate === null ? null : round($rate, 2),
        ];

        if ($errors !== []) {
            return $line(self::STATUS_ERROR, $data + ['amount' => null], $errors);
        }

        foreach (self::LIMITS as $field => $limit) {
            if (mb_strlen($data[$field]) > $limit) {
                $data[$field] = mb_substr($data[$field], 0, $limit);
                $messages[] = ucfirst(str_replace('_', ' ', $field))." was cut to {$limit} characters.";
            }
        }

        $data['amount'] = round($data['quantity'] * $data['rate'], 2);

        $sheetAmount = $this->parseNumber($raw['amount']);
        if ($sheetAmount !== null && $data['rate'] > 0 && abs($sheetAmount - $data['amount']) > max(1, abs($data['amount']) * 0.005)) {
            $messages[] = 'Sheet amount '.number_format($sheetAmount, 2).' differs from qty × rate '.number_format($data['amount'], 2).' — qty × rate is used.';
        }

        return $line(self::STATUS_IMPORT, $data, $messages);
    }

    /** "₹ 1,25,000.50/-" -> 125000.5; non-numbers -> null. */
    public function parseNumber(string $value): ?float
    {
        $clean = strtolower(trim($value));
        $clean = str_replace(['₹', 'rs.', 'rs', 'inr', '$', '€', '£', 'aed', 'usd', '/-', ',', ' ', "\u{00A0}"], '', $clean);
        $clean = rtrim($clean, '.');

        if ($clean === '' || ! preg_match('/^-?\d*\.?\d+(e[-+]?\d+)?$/', $clean)) {
            return null;
        }

        return (float) $clean;
    }

    private function isTotalRow(string $text): bool
    {
        return (bool) preg_match('/^\s*(sub[\s\-]*total|grand\s+total|total|carried\s+(over|forward)|brought\s+forward|c\/f|b\/f)\b/i', $text);
    }

    /**
     * @param  list<string>  $keywords
     */
    private function headerMatches(string $field, string $text, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            $isShort = mb_strlen($keyword) <= 4;
            $matched = $isShort
                ? (bool) preg_match('/(^|\s)'.preg_quote($keyword, '/').'(\s|$)/', $text)
                : str_contains($text, $keyword);

            if (! $matched) {
                continue;
            }

            // "Unit rate" / "unit price" is a rate, not a unit column.
            if ($field === 'unit' && preg_match('/rate|price|cost|amount/', $text)) {
                return false;
            }

            // "Total quantity" is a quantity, not an amount column.
            if ($field === 'amount' && preg_match('/qty|quantity/', $text)) {
                return false;
            }

            return true;
        }

        return false;
    }

    private function normalizeHeader(string $text): string
    {
        $text = mb_strtolower($text);
        $text = preg_replace('/[^\p{L}\p{N}\/]+/u', ' ', $text) ?? $text;

        return trim(preg_replace('/\s+/', ' ', $text) ?? $text);
    }

    private function cleanText(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace('/[ \t\x{00A0}]+/u', ' ', $text) ?? $text;

        return trim(preg_replace('/\n{2,}/', "\n", $text) ?? $text);
    }
}
