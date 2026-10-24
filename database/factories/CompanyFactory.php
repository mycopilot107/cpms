<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    protected $model = Company::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(),
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->numerify('+91-##########'),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'country' => 'India',
            'currency' => 'INR',
            'gst_number' => strtoupper(fake()->bothify('##???####?#?#')),
            'logo' => null,
            'status' => Company::STATUS_ACTIVE,
            'trial_started_at' => now(),
            'trial_ends_at' => now()->copy()->addDays(Company::TRIAL_DAYS),
        ];
    }

    /** A company outside India that records costs in another currency. */
    public function inCountry(string $country, string $currency): static
    {
        return $this->state(fn (array $attributes) => [
            'country' => $country,
            'currency' => $currency,
        ]);
    }

    /** A company whose trial has already lapsed. */
    public function trialExpired(): static
    {
        return $this->state(fn (array $attributes) => [
            'trial_started_at' => now()->copy()->subDays(Company::TRIAL_DAYS + 5),
            'trial_ends_at' => now()->copy()->subDays(5),
        ]);
    }
}
