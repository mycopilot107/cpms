<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-3 months', '+1 month');
        $end = (clone $start)->modify('+'.fake()->numberBetween(30, 240).' days');

        return [
            // Explicit company_id: factories run without an authenticated user,
            // so the BelongsToCompany trait cannot infer the tenant here.
            'company_id' => Company::factory(),
            'name' => fake()->randomElement(['Skyline', 'Riverside', 'Metro', 'Green Valley', 'Harbor', 'Summit'])
                .' '.fake()->randomElement(['Tower', 'Residency', 'Plaza', 'Complex', 'Bridge', 'Villas']),
            'project_code' => strtoupper(fake()->unique()->bothify('PRJ-####')),
            'client_name' => fake()->company(),
            'client_contact' => fake()->phoneNumber(),
            'site_address' => fake()->address(),
            'start_date' => $start,
            'end_date' => $end,
            'budget' => fake()->numberBetween(500000, 50000000),
            'status' => fake()->randomElement(Project::STATUSES),
            'description' => fake()->sentence(12),
            'created_by' => null,
        ];
    }

    public function forCompany(Company $company): static
    {
        return $this->state(fn () => ['company_id' => $company->id]);
    }

    public function status(string $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }
}
