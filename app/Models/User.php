<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Cashier\Billable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use Billable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'consent_at' => 'datetime',
        'consent_preferences' => 'array',
        'notification_preferences' => 'array',
    ];

    /**
     * The tenants the user belongs to.
     */
    public function tenants()
    {
        return $this->belongsToMany(Tenant::class)->withTimestamps()->withPivot('role');
    }

    /**
     * Posts created by this user.
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Roles assigned to this user.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Notifications for this user.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission($permission)
    {
        return $this->roles()->whereJsonContains('permissions', $permission)->exists();
    }

    /**
     * Check if user has any of the given permissions.
     */
    public function hasAnyPermission($permissions)
    {
        return $this->roles()->where(function ($query) use ($permissions) {
            foreach ($permissions as $permission) {
                $query->orWhereJsonContains('permissions', $permission);
            }
        })->exists();
    }

    /**
     * Get user's role in the current tenant.
     */
    public function getCurrentRole()
    {
        if (!app()->bound('currentTenant')) {
            return null;
        }
        
        return $this->roles()->where('tenant_id', app('currentTenant')->id)->first();
    }
}