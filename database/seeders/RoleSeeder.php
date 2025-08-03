<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Create permissions
        $permissions = [
            ['name' => 'posts.view', 'display_name' => 'View Posts', 'description' => 'Can view posts'],
            ['name' => 'posts.create', 'display_name' => 'Create Posts', 'description' => 'Can create posts'],
            ['name' => 'posts.edit', 'display_name' => 'Edit Posts', 'description' => 'Can edit posts'],
            ['name' => 'posts.delete', 'display_name' => 'Delete Posts', 'description' => 'Can delete posts'],
            ['name' => 'posts.publish', 'display_name' => 'Publish Posts', 'description' => 'Can publish posts'],
            ['name' => 'campaigns.view', 'display_name' => 'View Campaigns', 'description' => 'Can view campaigns'],
            ['name' => 'campaigns.create', 'display_name' => 'Create Campaigns', 'description' => 'Can create campaigns'],
            ['name' => 'campaigns.edit', 'display_name' => 'Edit Campaigns', 'description' => 'Can edit campaigns'],
            ['name' => 'campaigns.delete', 'display_name' => 'Delete Campaigns', 'description' => 'Can delete campaigns'],
            ['name' => 'analytics.view', 'display_name' => 'View Analytics', 'description' => 'Can view analytics'],
            ['name' => 'approvals.view', 'display_name' => 'View Approvals', 'description' => 'Can view approvals'],
            ['name' => 'approvals.approve', 'display_name' => 'Approve Content', 'description' => 'Can approve content'],
            ['name' => 'approvals.reject', 'display_name' => 'Reject Content', 'description' => 'Can reject content'],
            ['name' => 'social_accounts.manage', 'display_name' => 'Manage Social Accounts', 'description' => 'Can manage social accounts'],
            ['name' => 'users.manage', 'display_name' => 'Manage Users', 'description' => 'Can manage users'],
            ['name' => 'billing.manage', 'display_name' => 'Manage Billing', 'description' => 'Can manage billing'],
            ['name' => 'settings.manage', 'display_name' => 'Manage Settings', 'description' => 'Can manage settings'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate($permission);
        }

        // Create default roles for each tenant
        $tenants = \App\Models\Tenant::all();
        
        foreach ($tenants as $tenant) {
            // Owner role - full permissions
            $ownerRole = Role::firstOrCreate([
                'tenant_id' => $tenant->id,
                'name' => 'owner',
            ], [
                'display_name' => 'Owner',
                'description' => 'Full access to all features',
                'permissions' => Permission::pluck('name')->toArray(),
            ]);

            // Admin role - most permissions except billing
            $adminRole = Role::firstOrCreate([
                'tenant_id' => $tenant->id,
                'name' => 'admin',
            ], [
                'display_name' => 'Admin',
                'description' => 'Can manage content and users',
                'permissions' => Permission::whereNotIn('name', ['billing.manage'])->pluck('name')->toArray(),
            ]);

            // Editor role - content management
            $editorRole = Role::firstOrCreate([
                'tenant_id' => $tenant->id,
                'name' => 'editor',
            ], [
                'display_name' => 'Editor',
                'description' => 'Can create and edit content',
                'permissions' => [
                    'posts.view', 'posts.create', 'posts.edit', 'posts.publish',
                    'campaigns.view', 'campaigns.create', 'campaigns.edit',
                    'analytics.view', 'approvals.view',
                ],
            ]);

            // Approver role - approval workflow
            $approverRole = Role::firstOrCreate([
                'tenant_id' => $tenant->id,
                'name' => 'approver',
            ], [
                'display_name' => 'Approver',
                'description' => 'Can approve content',
                'permissions' => [
                    'posts.view', 'approvals.view', 'approvals.approve', 'approvals.reject',
                    'analytics.view',
                ],
            ]);

            // Viewer role - read-only access
            $viewerRole = Role::firstOrCreate([
                'tenant_id' => $tenant->id,
                'name' => 'viewer',
            ], [
                'display_name' => 'Viewer',
                'description' => 'Read-only access to content',
                'permissions' => [
                    'posts.view', 'campaigns.view', 'analytics.view',
                ],
            ]);

            // Assign owner role to tenant owner
            if ($tenant->owner_id) {
                $owner = \App\Models\User::find($tenant->owner_id);
                if ($owner) {
                    $owner->roles()->sync([$ownerRole->id]);
                }
            }
        }
    }
} 