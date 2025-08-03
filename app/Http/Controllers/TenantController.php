<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Plan;

/**
 * TenantController handles tenant management for multi‑tenant users.
 */
class TenantController extends Controller
{
    /**
     * Display a listing of tenants for the current user.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $tenants = $user->tenants()->with(['owner', 'plan'])->get();
        
        if ($request->wantsJson()) {
            return response()->json($tenants);
        }
        
        return view('tenants.index', compact('tenants'));
    }

    /**
     * Show the form for creating a new tenant.
     */
    public function create()
    {
        $plans = Plan::all();
        return view('tenants.create', compact('plans'));
    }

    /**
     * Store a newly created tenant.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:tenants,slug',
            'plan_id' => 'nullable|exists:plans,id',
        ]);
        
        $user = auth()->user();
        
        $tenant = Tenant::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'owner_id' => $user->id,
            'plan_id' => $validated['plan_id'] ?? null,
        ]);
        
        // Attach the creating user as owner
        $tenant->users()->attach($user->id, ['role' => 'owner']);
        
        if ($request->wantsJson()) {
            return response()->json($tenant, 201);
        }
        
        return redirect()->route('tenants.index')->with('success', 'Tenant created successfully!');
    }

    /**
     * Show the form for editing the specified tenant.
     */
    public function edit(Tenant $tenant)
    {
        // Ensure user has permission to edit this tenant
        $user = auth()->user();
        if (!$user->tenants()->where('tenants.id', $tenant->id)->exists()) {
            abort(403, 'You do not have permission to edit this tenant.');
        }
        
        $plans = Plan::all();
        $users = User::all(); // For adding/removing users
        $tenantUsers = $tenant->users()->withPivot('role')->get();
        
        return view('tenants.edit', compact('tenant', 'plans', 'users', 'tenantUsers'));
    }

    /**
     * Update the specified tenant.
     */
    public function update(Request $request, Tenant $tenant)
    {
        // Ensure user has permission to update this tenant
        $user = auth()->user();
        if (!$user->tenants()->where('tenants.id', $tenant->id)->exists()) {
            abort(403, 'You do not have permission to update this tenant.');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:tenants,slug,' . $tenant->id,
            'plan_id' => 'nullable|exists:plans,id',
        ]);
        
        $tenant->update($validated);
        
        if ($request->wantsJson()) {
            return response()->json($tenant);
        }
        
        return redirect()->route('tenants.index')->with('success', 'Tenant updated successfully!');
    }

    /**
     * Remove the specified tenant.
     */
    public function destroy(Tenant $tenant)
    {
        // Ensure user has permission to delete this tenant
        $user = auth()->user();
        if (!$user->tenants()->where('tenants.id', $tenant->id)->wherePivot('role', 'owner')->exists()) {
            abort(403, 'Only tenant owners can delete tenants.');
        }
        
        $tenant->delete();
        
        if (request()->wantsJson()) {
            return response()->json(['message' => 'Tenant deleted successfully.']);
        }
        
        return redirect()->route('tenants.index')->with('success', 'Tenant deleted successfully!');
    }

    /**
     * Switch the current tenant by storing the selected tenant ID in the session.
     */
    public function switch(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
        ]);
        $user = $request->user();
        // Ensure the authenticated user belongs to the requested tenant.
        if (!$user->tenants()->where('tenants.id', $validated['tenant_id'])->exists()) {
            abort(403, 'You do not belong to that tenant.');
        }
        $request->session()->put('tenant_id', $validated['tenant_id']);
        return redirect()->back();
    }
}