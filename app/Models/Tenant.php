<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'owner_id',
        'slug',
        'plan_id',
        'stripe_id',
        'pm_type',
        'pm_last_four',
        'trial_ends_at',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
        'trial_ends_at' => 'datetime',
    ];

    /**
     * The users belonging to this tenant.
     */
    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps()->withPivot('role');
    }

    /**
     * The owner of this tenant.
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * The subscription plan associated with this tenant.
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Roles defined for this tenant.
     */
    public function roles()
    {
        return $this->hasMany(Role::class);
    }

    /**
     * Check if tenant has an active subscription.
     */
    public function hasActiveSubscription()
    {
        return !is_null($this->stripe_id) && 
               (is_null($this->trial_ends_at) || $this->trial_ends_at->isFuture());
    }

    /**
     * Check if tenant is on trial.
     */
    public function onTrial()
    {
        return !is_null($this->trial_ends_at) && $this->trial_ends_at->isFuture();
    }
}