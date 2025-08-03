<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
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
            'social_account_id' => null,
            'campaign_id' => null,
            'content' => fake()->paragraph(),
            'media_path' => null,
            'status' => fake()->randomElement(['draft', 'scheduled', 'published', 'failed']),
            'external_id' => null,
            'alt_text' => null,
            'captions_path' => null,
            'variant_of' => null
        ];
    }
} 