<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Campaign;

/**
 * Controller for managing campaigns.  Campaigns group multiple posts under a
 * common goal or timeline.  Each campaign is scoped to the current tenant.
 */
class CampaignController extends Controller
{
    /**
     * Display a listing of the campaigns for the current tenant.
     */
    public function index(Request $request)
    {
        $campaigns = Campaign::where('tenant_id', app('currentTenant')->id)
            ->orderByDesc('created_at')
            ->paginate(20);
            
        if ($request->wantsJson()) {
            return response()->json($campaigns);
        }
        
        return view('campaigns.index', compact('campaigns'));
    }

    /**
     * Show the form for creating a new campaign.
     */
    public function create()
    {
        return view('campaigns.create');
    }

    /**
     * Store a newly created campaign in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'goal' => 'nullable|string|max:1000',
        ]);
        
        $campaign = Campaign::create([
            'tenant_id' => app('currentTenant')->id,
            'name' => $validated['name'],
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'goal' => $validated['goal'] ?? null,
        ]);
        
        if ($request->wantsJson()) {
            return response()->json($campaign, 201);
        }
        
        return redirect()->route('campaigns.index')->with('success', 'Campaign created successfully!');
    }

    /**
     * Display the specified campaign.
     */
    public function show(Campaign $campaign)
    {
        if (request()->wantsJson()) {
            return response()->json($campaign);
        }
        
        return view('campaigns.show', compact('campaign'));
    }

    /**
     * Show the form for editing the specified campaign.
     */
    public function edit(Campaign $campaign)
    {
        return view('campaigns.edit', compact('campaign'));
    }

    /**
     * Update the specified campaign in storage.
     */
    public function update(Request $request, Campaign $campaign)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'start_date' => 'sometimes|nullable|date',
            'end_date' => 'sometimes|nullable|date',
            'goal' => 'sometimes|nullable|string|max:1000',
        ]);
        
        $campaign->fill($validated);
        $campaign->save();
        
        if ($request->wantsJson()) {
            return response()->json($campaign);
        }
        
        return redirect()->route('campaigns.index')->with('success', 'Campaign updated successfully!');
    }

    /**
     * Remove the specified campaign from storage.
     */
    public function destroy(Campaign $campaign)
    {
        $campaign->delete();
        
        if (request()->wantsJson()) {
            return response()->json(['message' => 'Campaign deleted.']);
        }
        
        return redirect()->route('campaigns.index')->with('success', 'Campaign deleted successfully!');
    }
}