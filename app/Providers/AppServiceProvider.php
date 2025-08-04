<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Simple admin bypass for testing
        Gate::before(function ($user, $ability) {
            if ($user && $user->hasPermission('admin')) {
                return true;
            }
        });

        // Production optimizations
        if (app()->environment('production')) {
            \URL::forceScheme('https');
            app('currentTenant', null); // Will be set by middleware
        }
    }
} 