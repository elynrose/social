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