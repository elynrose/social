<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PostPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the given user can view the post.
     */
    public function view(User $user, Post $post): bool
    {
        // A user may view a post if they belong to the post's tenant.
        return $user->tenants()->where('tenants.id', $post->tenant_id)->exists();
    }

    /**
     * Determine if the given user can create posts.
     */
    public function create(User $user): bool
    {
        // Any authenticated user can create posts for their current tenant
        return app()->bound('currentTenant');
    }

    /**
     * Determine if the given user can update the post.
     */
    public function update(User $user, Post $post): bool
    {
        // A user may update a post if they belong to the post's tenant
        // and are the post creator or have admin/editor role
        if (!$user->tenants()->where('tenants.id', $post->tenant_id)->exists()) {
            return false;
        }

        // Post creator can always update
        if ($user->id === $post->user_id) {
            return true;
        }

        // Check if user has admin/editor role in this tenant
        $tenantUser = $user->tenants()->where('tenants.id', $post->tenant_id)->first();
        if ($tenantUser && in_array($tenantUser->pivot->role, ['owner', 'admin', 'editor'])) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the given user can delete the post.
     */
    public function delete(User $user, Post $post): bool
    {
        // A user may delete a post if they belong to the post's tenant
        // and are the post creator or have admin role
        if (!$user->tenants()->where('tenants.id', $post->tenant_id)->exists()) {
            return false;
        }

        // Post creator can always delete
        if ($user->id === $post->user_id) {
            return true;
        }

        // Check if user has admin role in this tenant
        $tenantUser = $user->tenants()->where('tenants.id', $post->tenant_id)->first();
        if ($tenantUser && in_array($tenantUser->pivot->role, ['owner', 'admin'])) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the given user can comment on the post.
     */
    public function comment(User $user, Post $post): bool
    {
        $tenantUser = $user->tenants()->where('tenants.id', $post->tenant_id)->first();
        if (! $tenantUser) {
            return false;
        }
        $role = $tenantUser->pivot->role;
        // Only owners, admins and editors may comment.  Viewers cannot.
        return in_array($role, ['owner', 'admin', 'editor']);
    }
}