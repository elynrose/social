<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@socialmediaos.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'consent_at' => now(),
            'consent_preferences' => ['marketing' => true, 'analytics' => true],
            'timezone' => 'UTC',
            'notification_preferences' => ['email' => true, 'in_app' => true],
        ]);

        // Create regular user
        User::create([
            'name' => 'John Doe',
            'email' => 'john@socialmediaos.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'consent_at' => now(),
            'consent_preferences' => ['marketing' => true, 'analytics' => true],
            'timezone' => 'America/New_York',
            'notification_preferences' => ['email' => true, 'in_app' => true],
        ]);

        // Create test user
        User::create([
            'name' => 'Test User',
            'email' => 'test@socialmediaos.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'consent_at' => now(),
            'consent_preferences' => ['marketing' => false, 'analytics' => true],
            'timezone' => 'Europe/London',
            'notification_preferences' => ['email' => false, 'in_app' => true],
        ]);

        $this->command->info('Users seeded successfully!');
        $this->command->info('Admin: admin@socialmediaos.com / password');
        $this->command->info('User: john@socialmediaos.com / password');
        $this->command->info('Test: test@socialmediaos.com / password');
    }
} 