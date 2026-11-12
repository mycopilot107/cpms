<?php

namespace App\Services;

use App\Models\Project;
use Carbon\Carbon;
use RuntimeException;

/**
 * Turns a spreadsheet of projects into rows that can be saved. The first row
 * that contains a project-name column is the header. Blank rows are ignored.
 */
class ProjectSheetParser
{
    public const STATUS_IMPORT = 'import';

    public const STATUS_ERROR = 'error';

    public const MAX_ROWS = 500;

    /**
     * Two example projects. Dates are written as text (YYYY-MM-DD) so Excel
     * does not turn them into serial numbers.
     *
     * @var list<list<string>>
     */
    public const SAMPLE_ROWS = [
        ['Project name', 'Project code', 'Client name', 'Client contact', 'Site address', 'Start date', 'Target end date', 'Budget', 'Status', 'Description'],
        ['Riverside Residency', 'PRJ-1001', 'Sharma Builders', '9876543210', 'Plot 12, Ring Road, Indore', '2026-04-01', '2027-03-31', '25000000', 'active', '80-flat residential tower'],
        ['Metro Bridge', 'PRJ-1002', 'City PWD', 'engineer@city.test', 'NH-52 km 14', '2026-06-01', '2026-12-15', '8400000', 'planning', 'Approach road and pier foundation'],
    ];

    /**
     * Normalised heading => project attribute.
     *
     * @var array<string, string>
     */
    private const HEADINGS = [
        'project name' => 'name',
        'name' => 'name',
        'project' => 'name',
        'project code' => 'project_code',
        'code' => 'project_code',
        'client name' => 'client_name',
        'client' => 'client_name',
        'client contact' => 'client_contact',
        'contact' => 'client_contact',
        'phone' => 'client_contact',
        'site address' => 'site_address',
        'address' => 'site_address',
        'site' => 'site_address',
        'start date' => 'start_date',
        'start' => 'start_date',
        'target end date' => 'end_date',
        'end date' => 'end_date',
        'end' => 'end_date',
        'budget' => 'budget',
        'status' => 'status',
        'description' => 'description',
        'notes' => 'description',
    ];

    /**
     * @var array<string, string>
     */
    private const STATUSES = [
        'planning' => Project::STATUS_PLANNING,
        'plan' => Project::STATUS_PLANNING,
        'active' => Project::STATUS_ACTIVE,
        'ongoing' => Project::STATUS_ACTIVE,
        'in progress' => Project::STATUS_ACTIVE,
        'on hold' => Project::STATUS_ON_HOLD,
        'onhold' => Project::STATUS_ON_HOLD,
        'hold' => Project::STATUS_ON_HOLD,
        'paused' => Project::STATUS_ON_HOLD,
        'completed' => Project::STATUS_COMPLETED,
        'complete' => Project::STATUS_COMPLETED,
        'done' => Project::STATUS_COMPLETED,
        'cancelled' => Project::STATUS_CANCELLED,
        'canceled' => Project::STATUS_CANCELLED,
    ];

    /**
     * @param  list<list<string>>  $rows
     * @param  list<string>  $existingCodes  project codes already used in this company
     * @return array{lines: list<array{row: int, status: string, messages: list<string>, data: array<string, mixed>, label: string}>, summary: array{import: int, error: int}}
     */
    public function parse(array $rows, array $existingCodes = []): array
    {
        $headerIndex = $this->headerIndex($rows);
        if ($headerIndex === null) {
            throw new RuntimeException('No header row found. The sheet needs a "Project name" column. Download the sample file and keep its headings.');
        }

        $columns = $this->columns($rows[$headerIndex]);
        $existing = [];
        foreach ($existingCodes as $code) {
            $existing[$this->codeKey($code)] = true;
        }

        $lines = [];
        $seenCodes = [];
        $dataRows = 0;

        foreach ($rows as $index => $row) {
            if ($index <= $headerIndex || $this->isBlank($row)) {
                continue;
            }

            $dataRows++;
            if ($dataRows > self::MAX_ROWS) {
                throw new RuntimeException('This file has more than '.self::MAX_ROWS.' projects. Split it into smaller files.');
            }

            $values = [];
            foreach ($columns as $field => $column) {
                $values[$field] = trim((string) ($row[$column] ?? ''));
            }

            [$data, $messages] = $this->project($values);

            $codeKey = $data['project_code'] !== null ? $this->codeKey($data['project_code']) : null;
            if ($codeKey !== null && isset($seenCodes[$codeKey])) {
                $messages[] = 'Project code '.$data['project_code'].' is used more than once in this file.';
            }
            if ($codeKey !== null && isset($existing[$codeKey])) {
                $messages[] = 'Project code '.$data['project_code'].' is already used by a project in your company.';
            }
            if ($codeKey !== null) {
                $seenCodes[$codeKey] = true;
            }

            $lines[] = [
                'row' => $index + 1,
                'status' => $messages === [] ? self::STATUS_IMPORT : self::STATUS_ERROR,
                'messages' => $messages,
                'data' => $data,
                'label' => $data['name'] !== '' ? $data['name'] : '(no name)',
            ];
        }

        if ($lines === []) {
            throw new RuntimeException('The sheet has a header but no projects. Add one row per project under the headings.');
        }

        return [
            'lines' => $lines,
            'summary' => [
                'import' => count(array_filter($lines, fn (array $line) => $line['status'] === self::STATUS_IMPORT)),
                'error' => count(array_filter($lines, fn (array $line) => $line['status'] === self::STATUS_ERROR)),
            ],
        ];
    }

    /**
     * @param  list<list<string>>  $rows
     */
    private function headerIndex(array $rows): ?int
    {
        foreach (array_slice($rows, 0, 10, true) as $index => $row) {
            foreach ($row as $cell) {
                if ((self::HEADINGS[$this->normalise($cell)] ?? null) === 'name') {
                    return $index;
                }
            }
        }

        return null;
    }

    /**
     * @param  list<string>  $header
     * @return array<string, int>
     */
    private function columns(array $header): array
    {
        $columns = [];
        foreach ($header as $index => $cell) {
            $field = self::HEADINGS[$this->normalise($cell)] ?? null;
            if ($field !== null && ! isset($columns[$field])) {
                $columns[$field] = $index;
            }
        }

        return $columns;
    }

    /**
     * @param  array<string, string>  $values
     * @return array{0: array<string, mixed>, 1: list<string>}
     */
    private function project(array $values): array
    {
        $messages = [];
        $name = $values['name'] ?? '';
        if ($name === '') {
            $messages[] = 'Project name is required.';
        } elseif (mb_strlen($name) > 255) {
            $messages[] = 'Project name is longer than 255 characters.';
        }

        $code = $this->limited($values['project_code'] ?? '', 50, 'Project code', $messages);
        $client = $this->limited($values['client_name'] ?? '', 255, 'Client name', $messages);
        $contact = $this->limited($values['client_contact'] ?? '', 255, 'Client contact', $messages);
        $address = $this->limited($values['site_address'] ?? '', 500, 'Site address', $messages);
        $description = $this->limited($values['description'] ?? '', 5000, 'Description', $messages);

        $start = $this->date($values['start_date'] ?? '', 'Start date', $messages);
        $end = $this->date($values['end_date'] ?? '', 'Target end date', $messages);
        if ($start !== null && $end !== null && $end < $start) {
            $messages[] = 'Target end date is before the start date.';
        }

        $status = $this->status($values['status'] ?? '', $messages);
        $budget = $this->budget($values['budget'] ?? '', $messages);

        return [[
            'name' => $name,
            'project_code' => $code !== '' ? $code : null,
            'client_name' => $client !== '' ? $client : null,
            'client_contact' => $contact !== '' ? $contact : null,
            'site_address' => $address !== '' ? $address : null,
            'start_date' => $start,
            'end_date' => $end,
            'budget' => $budget,
            'status' => $status,
            'description' => $description !== '' ? $description : null,
        ], $messages];
    }

    /**
     * @param  list<string>  $messages
     */
    private function limited(string $value, int $max, string $label, array &$messages): string
    {
        if (mb_strlen($value) > $max) {
            $messages[] = $label.' is longer than '.$max.' characters.';
        }

        return $value;
    }

    /**
     * @param  list<string>  $messages
     */
    private function date(string $value, string $label, array &$messages): ?string
    {
        if ($value === '') {
            return null;
        }

        if (preg_match('/^\d+(\.0+)?$/', $value) === 1) {
            $serial = (int) $value;
            if ($serial >= 20000 && $serial <= 80000) {
                return Carbon::create(1899, 12, 30)->addDays($serial)->toDateString();
            }
        }

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'd/m/y', 'd-m-y'] as $format) {
            $date = \DateTimeImmutable::createFromFormat('!'.$format, $value);
            $errors = \DateTimeImmutable::getLastErrors();
            if ($date !== false && ($errors === false || (($errors['warning_count'] ?? 0) === 0 && ($errors['error_count'] ?? 0) === 0))) {
                return $date->format('Y-m-d');
            }
        }

        $messages[] = $label.' must be a date like 2026-04-01 or 01/04/2026.';

        return null;
    }

    /**
     * @param  list<string>  $messages
     */
    private function status(string $value, array &$messages): string
    {
        if ($value === '') {
            return Project::STATUS_PLANNING;
        }

        $status = self::STATUSES[$this->normalise($value)] ?? null;
        if ($status === null) {
            $messages[] = 'Status must be planning, active, on hold, completed, or cancelled.';

            return Project::STATUS_PLANNING;
        }

        return $status;
    }

    /**
     * @param  list<string>  $messages
     */
    private function budget(string $value, array &$messages): ?string
    {
        if ($value === '') {
            return null;
        }

        $clean = str_replace([',', ' '], '', $value);
        $clean = preg_replace('/[^\d.\-]/', '', $clean) ?? '';
        if ($clean === '' || ! is_numeric($clean) || (float) $clean < 0) {
            $messages[] = 'Budget must be a number, zero or more.';

            return null;
        }

        return number_format((float) $clean, 2, '.', '');
    }

    /**
     * @param  list<string>  $row
     */
    private function isBlank(array $row): bool
    {
        return implode('', $row) === '';
    }

    private function normalise(string $value): string
    {
        $value = strtolower(trim($value));
        $value = str_replace(['_', '-'], ' ', $value);
        $value = preg_replace('/[^a-z0-9 ]/', '', $value) ?? $value;

        return preg_replace('/\s+/', ' ', $value) ?? $value;
    }

    private function codeKey(string $code): string
    {
        return mb_strtolower(trim($code));
    }
}
