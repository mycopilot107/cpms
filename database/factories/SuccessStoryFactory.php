<?php

namespace Database\Factories;

use App\Models\SuccessStory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SuccessStory>
 */
class SuccessStoryFactory extends Factory
{
    protected $model = SuccessStory::class;

    public function definition(): array
    {
        return [
            'name'         => fake()->name(),
            'role'         => fake()->jobTitle(),
            'organisation' => fake()->company(),
            'email'        => fake()->safeEmail(),
            'story'        => fake()->paragraphs(2, true),
            'rating'       => fake()->numberBetween(3, 5),
            'status'       => SuccessStory::STATUS_PENDING,
            'is_featured'  => false,
            'published_at' => null,
            'company_id'   => null,
            'submitted_by' => null,
        ];
    }

    /** An approved (public-ready) story. */
    public function approved(): static
    {
        return $this->state(fn () => [
            'status'       => SuccessStory::STATUS_APPROVED,
            'published_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => ['status' => SuccessStory::STATUS_REJECTED]);
    }

    public function featured(): static
    {
        return $this->state(fn () => [
            'status'       => SuccessStory::STATUS_APPROVED,
            'is_featured'  => true,
            'published_at' => now(),
        ]);
    }
}
