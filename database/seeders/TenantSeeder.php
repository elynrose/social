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
            'domain' => 'social-media-os.local',
            'database' => 'social_media_os_main',
        ]);

        // Create demo tenant
        $demoTenant = Tenant::create([
            'name' => 'Demo Company',
            'domain' => 'demo-company.local',
            'database' => 'social_media_os_demo',
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

        // Also attach admin user to both tenants
        $mainTenant->users()->attach($adminUser->id, ['role' => 'admin']);
        $demoTenant->users()->attach($adminUser->id, ['role' => 'admin']);

        $this->command->info('Tenants seeded successfully!');
        $this->command->info('Main Tenant: Social Media OS (Domain: social-media-os.local)');
        $this->command->info('Demo Tenant: Demo Company (Domain: demo-company.local)');
    }
} 