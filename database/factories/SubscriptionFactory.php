<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subscription>
 */
class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'plan' => Subscription::PLAN_TRIAL,
            'status' => Subscription::STATUS_TRIAL,
            'trial_starts_at' => now(),
            'trial_ends_at' => now()->copy()->addDays(Company::TRIAL_DAYS),
            'starts_at' => null,
            'ends_at' => null,
        ];
    }
}
