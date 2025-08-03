<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mention;
use App\Models\SocialAccount;

class MentionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $tenantIds = $user->tenants->pluck('id');
        
        $query = Mention::whereIn('tenant_id', $tenantIds);
        
        // Apply filters
        if ($platform = $request->get('platform')) {
            $query->where('platform', $platform);
        }
        if ($sentiment = $request->get('sentiment')) {
            $query->where('sentiment', $sentiment);
        }
        if ($date = $request->get('date')) {
            $query->whereDate('posted_at', $date);
        }
        
        $mentions = $query->orderByDesc('posted_at')->paginate(20);
        
        // Get available platforms for filtering
        $platforms = Mention::whereIn('tenant_id', $tenantIds)
            ->distinct()
            ->pluck('platform')
            ->filter()
            ->values();
            
        $sentiments = ['positive', 'negative', 'neutral'];

        if ($request->wantsJson()) {
            return response()->json(compact('mentions', 'platforms', 'sentiments'));
        }

        return view('mentions.index', compact('mentions', 'platforms', 'sentiments'));
    }

    public function show(Request $request, Mention $mention)
    {
        $user = auth()->user();
        $tenantIds = $user->tenants->pluck('id');
        
        // Ensure user can only view mentions for their tenants
        if (!in_array($mention->tenant_id, $tenantIds->toArray())) {
            abort(403, 'Unauthorized action.');
        }

        if ($request->wantsJson()) {
            return response()->json($mention);
        }

        return view('mentions.show', compact('mention'));
    }

    public function update(Request $request, Mention $mention)
    {
        $user = auth()->user();
        $tenantIds = $user->tenants->pluck('id');
        
        // Ensure user can only update mentions for their tenants
        if (!in_array($mention->tenant_id, $tenantIds->toArray())) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'status' => 'required|in:new,reviewed,responded,ignored',
            'notes' => 'nullable|string|max:500',
        ]);

        $mention->update([
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        if ($request->wantsJson()) {
            return response()->json($mention);
        }

        return redirect()->route('mentions.index')
            ->with('success', 'Mention updated successfully.');
    }

    public function destroy(Request $request, Mention $mention)
    {
        $user = auth()->user();
        $tenantIds = $user->tenants->pluck('id');
        
        // Ensure user can only delete mentions for their tenants
        if (!in_array($mention->tenant_id, $tenantIds->toArray())) {
            abort(403, 'Unauthorized action.');
        }

        $mention->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Mention deleted successfully.']);
        }

        return redirect()->route('mentions.index')
            ->with('success', 'Mention deleted successfully.');
    }

    public function fetch(Request $request)
    {
        $user = $request->user();
        $query = Mention::whereIn('tenant_id', $user->tenants->pluck('id'));
        
        if ($platform = $request->get('platform')) {
            $query->where('platform', $platform);
        }
        if ($sentiment = $request->get('sentiment')) {
            $query->where('sentiment', $sentiment);
        }
        
        $mentions = $query->orderByDesc('posted_at')->paginate(20);
        
        if ($request->wantsJson()) {
            return response()->json($mentions);
        }

        return view('mentions.fetch', compact('mentions'));
    }

    public function analytics(Request $request)
    {
        $user = $request->user();
        $tenantIds = $user->tenants->pluck('id');
        
        $analytics = [
            'total_mentions' => Mention::whereIn('tenant_id', $tenantIds)->count(),
            'positive_mentions' => Mention::whereIn('tenant_id', $tenantIds)->where('sentiment', 'positive')->count(),
            'negative_mentions' => Mention::whereIn('tenant_id', $tenantIds)->where('sentiment', 'negative')->count(),
            'neutral_mentions' => Mention::whereIn('tenant_id', $tenantIds)->where('sentiment', 'neutral')->count(),
            'platform_breakdown' => Mention::whereIn('tenant_id', $tenantIds)
                ->selectRaw('platform, COUNT(*) as count')
                ->groupBy('platform')
                ->get(),
        ];

        if ($request->wantsJson()) {
            return response()->json($analytics);
        }

        return view('mentions.analytics', compact('analytics'));
    }
}