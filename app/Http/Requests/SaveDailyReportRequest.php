<?php

namespace App\Http\Requests;

use App\Models\DailyReport;
use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * A daily site report from the app. Labour can be sent per trade (the total
 * is then their sum) or as a single count.
 */
class SaveDailyReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', DailyReport::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'project_id' => [
                'required',
                Rule::exists('projects', 'id')
                    ->where('company_id', $this->user()->company_id)
                    ->whereNotIn('status', [Project::STATUS_COMPLETED, Project::STATUS_CANCELLED]),
            ],
            'report_date' => [
                'required',
                'date_format:Y-m-d',
                'before_or_equal:today',
                'after_or_equal:'.today()->subDays(DailyReport::EDIT_WINDOW_DAYS)->toDateString(),
            ],
            'work_done' => ['required', 'string', 'max:5000'],
            'labour' => ['nullable', 'array', 'max:30'],
            'labour.*.trade' => ['required', 'string', 'max:50'],
            'labour.*.count' => ['required', 'integer', 'min:0', 'max:2000'],
            'labour_count' => [Rule::requiredIf(fn () => ! $this->filled('labour')), 'nullable', 'integer', 'min:0', 'max:20000'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'project_id.exists' => 'Choose an open project of your company.',
            'report_date.before_or_equal' => 'A report cannot be for a future date.',
            'report_date.after_or_equal' => 'Reports can be filed up to '.DailyReport::EDIT_WINDOW_DAYS.' days late.',
            'work_done.required' => 'Write what happened on site today.',
            'labour_count.required' => 'Enter how many labour were on site.',
        ];
    }

    /**
     * Attributes for the report, with labour totalled from the trades.
     *
     * @return array{project_id: int, report_date: string, work_done: string, labour_count: int, labour_breakdown: ?list<array{trade: string, count: int}>, notes: ?string}
     */
    public function reportAttributes(): array
    {
        $data = $this->validated();

        $breakdown = collect($data['labour'] ?? [])
            ->map(fn (array $row) => ['trade' => trim($row['trade']), 'count' => (int) $row['count']])
            ->filter(fn (array $row) => $row['trade'] !== '' && $row['count'] > 0)
            ->groupBy('trade')
            ->map(fn ($rows, $trade) => ['trade' => $trade, 'count' => $rows->sum('count')])
            ->values()
            ->all();

        return [
            'project_id' => (int) $data['project_id'],
            'report_date' => $data['report_date'],
            'work_done' => trim($data['work_done']),
            'labour_count' => $breakdown !== [] ? array_sum(array_column($breakdown, 'count')) : (int) ($data['labour_count'] ?? 0),
            'labour_breakdown' => $breakdown !== [] ? $breakdown : null,
            'notes' => isset($data['notes']) && trim($data['notes']) !== '' ? trim($data['notes']) : null,
        ];
    }
}
