<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
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
            'user_id' => null,
            'type' => fake()->randomElement(['post_created', 'post_published', 'approval_requested', 'comment_added']),
            'title' => fake()->sentence(),
            'message' => fake()->paragraph(),
            'data' => [
                'type' => fake()->randomElement(['post_created', 'post_published', 'approval_requested', 'comment_added']),
                'action_url' => fake()->url()
            ],
            'read_at' => null
        ];
    }
} 