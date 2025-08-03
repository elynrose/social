<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'domain',
        'database',
    ];

    /**
     * The users belonging to this tenant.
     */
    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps()->withPivot('role');
    }

    /**
     * Check if tenant has an active subscription.
     */
    public function hasActiveSubscription()
    {
        return true; // Simplified for now
    }

    /**
     * Check if tenant is on trial.
     */
    public function onTrial()
    {
        return false; // Simplified for now
    }
}