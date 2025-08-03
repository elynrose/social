<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ApiConfiguration;
use App\Models\Tenant;

class ApiConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mainTenant = Tenant::where('slug', 'social-media-os')->first();
        
        if ($mainTenant) {
            // Facebook API Configuration
            ApiConfiguration::create([
                'tenant_id' => $mainTenant->id,
                'platform' => 'facebook',
                'client_id' => 'demo_facebook_client_id',
                'client_secret' => 'demo_facebook_client_secret',
                'redirect_uri' => 'https://socialmediaos.com/oauth/facebook/callback',
                'scopes' => [
                    'pages_read_engagement',
                    'pages_manage_posts',
                    'pages_show_list',
                    'pages_read_user_content',
                    'pages_manage_metadata'
                ],
                'is_active' => true,
                'settings' => [
                    'api_version' => 'v18.0',
                    'webhook_verify_token' => 'demo_webhook_token',
                ],
            ]);

            // Twitter API Configuration
            ApiConfiguration::create([
                'tenant_id' => $mainTenant->id,
                'platform' => 'twitter',
                'client_id' => 'demo_twitter_client_id',
                'client_secret' => 'demo_twitter_client_secret',
                'redirect_uri' => 'https://socialmediaos.com/oauth/twitter/callback',
                'scopes' => [
                    'tweet.read',
                    'tweet.write',
                    'users.read',
                    'offline.access',
                    'dm.read',
                    'dm.write'
                ],
                'is_active' => true,
                'settings' => [
                    'api_version' => 'v2',
                    'webhook_verify_token' => 'demo_webhook_token',
                ],
            ]);

            // LinkedIn API Configuration
            ApiConfiguration::create([
                'tenant_id' => $mainTenant->id,
                'platform' => 'linkedin',
                'client_id' => 'demo_linkedin_client_id',
                'client_secret' => 'demo_linkedin_client_secret',
                'redirect_uri' => 'https://socialmediaos.com/oauth/linkedin/callback',
                'scopes' => [
                    'w_member_social',
                    'r_liteprofile',
                    'r_organization_social',
                    'w_organization_social'
                ],
                'is_active' => true,
                'settings' => [
                    'api_version' => 'v2',
                    'webhook_verify_token' => 'demo_webhook_token',
                ],
            ]);

            $this->command->info('API Configurations seeded successfully!');
            $this->command->info('Created configurations for: Facebook, Twitter, LinkedIn');
            $this->command->info('Note: These are demo configurations. Replace with real credentials for production.');
        } else {
            $this->command->warn('Main tenant not found. Skipping API configuration seeding.');
        }
    }
} 