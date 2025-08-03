<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\User;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the admin user to be the owner
        $adminUser = User::where('email', 'admin@socialmediaos.com')->first();
        
        if (!$adminUser) {
            $this->command->warn('Admin user not found. Creating tenants without owner.');
            return;
        }

        // Create main tenant
        $mainTenant = Tenant::create([
            'name' => 'Social Media OS',
            'owner_id' => $adminUser->id,
            'slug' => 'social-media-os',
            'settings' => [
                'timezone' => 'UTC',
                'date_format' => 'Y-m-d',
                'time_format' => 'H:i',
                'currency' => 'USD',
            ],
            'stripe_id' => null,
            'pm_type' => null,
            'pm_last_four' => null,
            'trial_ends_at' => now()->addDays(30),
            'plan_id' => null,
        ]);

        // Create demo tenant
        $demoTenant = Tenant::create([
            'name' => 'Demo Company',
            'owner_id' => $adminUser->id,
            'slug' => 'demo-company',
            'settings' => [
                'timezone' => 'America/New_York',
                'date_format' => 'm/d/Y',
                'time_format' => 'g:i A',
                'currency' => 'USD',
            ],
            'stripe_id' => null,
            'pm_type' => null,
            'pm_last_four' => null,
            'trial_ends_at' => now()->addDays(14),
            'plan_id' => null,
        ]);

        // Assign users to tenants through the pivot table
        $johnUser = User::where('email', 'john@socialmediaos.com')->first();
        if ($johnUser) {
            $mainTenant->users()->attach($johnUser->id, ['role' => 'admin']);
        }

        $testUser = User::where('email', 'test@socialmediaos.com')->first();
        if ($testUser) {
            $demoTenant->users()->attach($testUser->id, ['role' => 'admin']);
        }

        $this->command->info('Tenants seeded successfully!');
        $this->command->info('Main Tenant: Social Media OS (Owner: ' . $adminUser->name . ')');
        $this->command->info('Demo Tenant: Demo Company (Owner: ' . $adminUser->name . ')');
    }
} 