<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ApiConfiguration>
 */
class ApiConfigurationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $platform = fake()->randomElement(['facebook', 'twitter', 'linkedin', 'instagram', 'youtube', 'tiktok']);
        
        return [
            'tenant_id' => null,
            'platform' => $platform,
            'client_id' => fake()->regexify('[A-Za-z0-9]{20}'),
            'client_secret' => fake()->regexify('[A-Za-z0-9]{32}'),
            'redirect_uri' => fake()->url() . '/oauth/' . $platform . '/callback',
            'scopes' => \App\Models\ApiConfiguration::getDefaultScopes($platform),
            'is_active' => true,
            'settings' => []
        ];
    }
} 