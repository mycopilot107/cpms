<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-1 month', '+1 month');
        $due = (clone $start)->modify('+'.fake()->numberBetween(3, 45).' days');
        $status = fake()->randomElement(Task::STATUSES);

        return [
            'company_id' => Company::factory(),
            'project_id' => Project::factory(),
            'assigned_to' => null,
            'title' => ucfirst(fake()->words(fake()->numberBetween(2, 4), true)),
            'description' => fake()->sentence(10),
            'start_date' => $start,
            'due_date' => $due,
            'priority' => fake()->randomElement(Task::PRIORITIES),
            'status' => $status,
            'progress' => $status === Task::STATUS_COMPLETED
                ? 100
                : ($status === Task::STATUS_PENDING ? 0 : fake()->numberBetween(10, 90)),
            'estimated_hours' => fake()->numberBetween(4, 160),
            'actual_hours' => fake()->optional()->numberBetween(2, 180),
            'dependency_id' => null,
            'created_by' => null,
        ];
    }

    public function forProject(Project $project): static
    {
        return $this->state(fn () => [
            'company_id' => $project->company_id,
            'project_id' => $project->id,
        ]);
    }

    public function status(string $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }
}
