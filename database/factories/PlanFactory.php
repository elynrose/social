<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Plan>
 */
class PlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 9.99, 299.99),
            'billing_cycle' => fake()->randomElement(['monthly', 'yearly']),
            'features' => [
                'posts_per_month' => fake()->numberBetween(10, 1000),
                'social_accounts' => fake()->numberBetween(1, 50),
                'team_members' => fake()->numberBetween(1, 100),
                'analytics' => true,
                'ai_features' => true
            ],
            'stripe_price_id' => 'price_' . fake()->regexify('[A-Za-z0-9]{14}'),
            'is_active' => true
        ];
    }
} 