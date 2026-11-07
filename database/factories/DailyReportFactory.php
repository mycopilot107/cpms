<?php

namespace Database\Factories;

use App\Models\DailyReport;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DailyReport>
 */
class DailyReportFactory extends Factory
{
    protected $model = DailyReport::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'company_id' => fn (array $attributes) => Project::query()->withoutGlobalScopes()->find($attributes['project_id'])?->company_id,
            'user_id' => null,
            'report_date' => today(),
            'work_done' => fake()->sentence(10),
            'labour_count' => 12,
            'labour_breakdown' => [['trade' => 'Mason', 'count' => 4], ['trade' => 'Helper', 'count' => 8]],
            'notes' => null,
        ];
    }
}
