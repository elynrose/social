<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant;

class SetCurrentTenant
{
    /**
     * Handle an incoming request.
     * Determine the current tenant based on the authenticated user and bind it.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        
        // If no user is authenticated, skip tenant resolution
        if (!$user) {
            return $next($request);
        }
        
        // Check if currentTenant is already bound
        if (app()->bound('currentTenant')) {
            return $next($request);
        }
        
        try {
            $tenant = null;
            // Attempt to resolve tenant from session if the user has chosen one.
            $selectedId = $request->session()->get('tenant_id');
            if ($selectedId) {
                $tenant = $user->tenants()->where('tenants.id', $selectedId)->first();
            }
            // Fallback to subdomain detection if no session tenant found.
            if (!$tenant) {
                $host = $request->getHost();
                $parts = explode('.', $host);
                $subdomain = $parts[0] ?? null;
                // Avoid treating localhost or plain domains as subdomains
                if ($subdomain && !in_array($subdomain, ['localhost', '127', 'www'])) {
                    $candidate = Tenant::where('slug', $subdomain)->first();
                    if ($candidate && $user->tenants->contains($candidate)) {
                        $tenant = $candidate;
                        // Persist the selection to the session
                        $request->session()->put('tenant_id', $tenant->id);
                    }
                }
            }
            // Fallback to the first available tenant if none selected or invalid.
            if (!$tenant) {
                $tenant = $user->tenants()->first();
            }
            if ($tenant) {
                app()->instance('currentTenant', $tenant);
            } else {
                // If no tenant is found, try to create a default tenant
                try {
                    $defaultTenant = Tenant::create([
                        'name' => $user->name . "'s Workspace",
                        'owner_id' => $user->id,
                        'slug' => strtolower(str_replace(' ', '-', $user->name)) . '-' . time(),
                    ]);
                    
                    // Attach the user to this tenant
                    $user->tenants()->attach($defaultTenant->id, ['role' => 'owner']);
                    
                    app()->instance('currentTenant', $defaultTenant);
                } catch (\Exception $e) {
                    // Log the error and continue without tenant binding
                    \Log::error('Failed to create default tenant: ' . $e->getMessage());
                    // Don't bind a tenant if creation fails
                }
            }
        } catch (\Exception $e) {
            // Log the error and continue without tenant binding
            \Log::error('Tenant resolution failed: ' . $e->getMessage());
        }
        
        return $next($request);
    }
}