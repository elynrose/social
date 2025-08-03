<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Free Plan
        Plan::create([
            'name' => 'Free',
            'slug' => 'free',
            'price' => 0,
            'currency' => 'usd',
            'features' => [
                'social_accounts' => 2,
                'posts_per_month' => 50,
                'analytics' => false,
                'ai_features' => false,
                'team_members' => 1,
                'api_calls' => 100,
            ],
            'stripe_price_id' => null,
        ]);

        // Starter Plan
        Plan::create([
            'name' => 'Starter',
            'slug' => 'starter',
            'price' => 2900, // $29.00 in cents
            'currency' => 'usd',
            'features' => [
                'social_accounts' => 5,
                'posts_per_month' => 200,
                'analytics' => true,
                'ai_features' => true,
                'team_members' => 3,
                'api_calls' => 1000,
            ],
            'stripe_price_id' => 'price_starter_monthly',
        ]);

        // Professional Plan
        Plan::create([
            'name' => 'Professional',
            'slug' => 'professional',
            'price' => 7900, // $79.00 in cents
            'currency' => 'usd',
            'features' => [
                'social_accounts' => 15,
                'posts_per_month' => 1000,
                'analytics' => true,
                'ai_features' => true,
                'team_members' => 10,
                'api_calls' => 5000,
                'priority_support' => true,
            ],
            'stripe_price_id' => 'price_professional_monthly',
        ]);

        // Enterprise Plan
        Plan::create([
            'name' => 'Enterprise',
            'slug' => 'enterprise',
            'price' => 19900, // $199.00 in cents
            'currency' => 'usd',
            'features' => [
                'social_accounts' => -1, // unlimited
                'posts_per_month' => -1, // unlimited
                'analytics' => true,
                'ai_features' => true,
                'team_members' => -1, // unlimited
                'api_calls' => -1, // unlimited
                'priority_support' => true,
                'custom_integrations' => true,
                'dedicated_account_manager' => true,
            ],
            'stripe_price_id' => 'price_enterprise_monthly',
        ]);

        $this->command->info('Plans seeded successfully!');
        $this->command->info('Available plans: Free, Starter ($29), Professional ($79), Enterprise ($199)');
    }
} 