<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CommentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the given user can delete the comment.
     */
    public function delete(User $user, Comment $comment): bool
    {
        // The author may delete their own comment.
        if ($comment->user_id === $user->id) {
            return true;
        }
        // Owners and admins of the tenant can also delete any comment.
        $tenantUser = $user->tenants()->where('tenants.id', $comment->tenant_id)->first();
        if ($tenantUser) {
            $role = $tenantUser->pivot->role;
            return in_array($role, ['owner', 'admin']);
        }
        return false;
    }
}