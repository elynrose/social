<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SocialAccount>
 */
class SocialAccountFactory extends Factory
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
            'platform' => fake()->randomElement(['facebook', 'twitter', 'linkedin', 'instagram', 'youtube']),
            'account_id' => fake()->unique()->numerify('##########'),
            'username' => fake()->userName(),
            'access_token' => fake()->sha1(),
            'refresh_token' => fake()->sha1(),
            'token_expires_at' => fake()->dateTimeBetween('now', '+1 year')
        ];
    }
} 