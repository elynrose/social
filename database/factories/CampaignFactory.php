<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Campaign>
 */
class CampaignFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => null,
            'name' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'start_date' => fake()->dateTimeBetween('now', '+1 month'),
            'end_date' => fake()->dateTimeBetween('+1 month', '+3 months'),
            'status' => fake()->randomElement(['draft', 'active', 'paused', 'completed']),
            'budget' => fake()->numberBetween(100, 10000),
            'target_audience' => fake()->words(5, true),
            'goals' => fake()->paragraph()
        ];
    }
} 