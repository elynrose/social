<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Approval;
use App\Models\Engagement;
use App\Models\Mention;

class ComplianceController extends Controller
{
    /**
     * Display the compliance dashboard.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $tenantIds = $user->tenants->pluck('id');
        
        $complianceStats = [
            'total_posts' => Post::whereIn('tenant_id', $tenantIds)->count(),
            'total_comments' => Comment::whereIn('tenant_id', $tenantIds)->count(),
            'total_approvals' => Approval::whereIn('tenant_id', $tenantIds)->count(),
            'total_engagements' => Engagement::whereIn('tenant_id', $tenantIds)->count(),
            'total_mentions' => Mention::whereIn('tenant_id', $tenantIds)->count(),
        ];

        if ($request->wantsJson()) {
            return response()->json($complianceStats);
        }

        return view('compliance.index', compact('complianceStats'));
    }

    /**
     * Show compliance settings and policies.
     */
    public function settings(Request $request)
    {
        if ($request->wantsJson()) {
            return response()->json(['message' => 'Compliance settings available']);
        }

        return view('compliance.settings');
    }

    /**
     * Export all data associated with the authenticated user for GDPR/CCPA.
     * This returns a JSON structure containing the user profile,
     * posts, comments, approvals, engagements and mentions.  In a real
     * system you might stream a zip file or send the data via email.
     */
    public function export(Request $request)
    {
        $user = $request->user();
        $data = [];
        $data['user'] = $user->toArray();
        
        // Posts and related data
        $data['posts'] = $user->posts()->with(['comments', 'approvals', 'engagements', 'mentions'])->get()->toArray();
        
        // Social accounts
        $data['social_accounts'] = $user->tenants->flatMap(function ($tenant) {
            return $tenant->socialAccounts ?? [];
        })->values()->toArray();

        if ($request->wantsJson()) {
            return response()->json($data);
        }

        // For web requests, offer download
        $filename = 'user_data_' . $user->id . '_' . now()->format('Y-m-d_H-i-s') . '.json';
        
        return response($data)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Show data deletion confirmation page.
     */
    public function deleteConfirm(Request $request)
    {
        if ($request->wantsJson()) {
            return response()->json(['message' => 'Delete confirmation required']);
        }

        return view('compliance.delete-confirm');
    }

    /**
     * Delete all data associated with the authenticated user.  This
     * operation cannot be undone, so ensure the user has confirmed
     * before calling this endpoint.  Note that deleting a user may
     * orphan posts or other resources belonging to a tenant; you can
     * adjust this logic to soft delete instead.
     */
    public function delete(Request $request)
    {
        $user = $request->user();
        
        // Validate confirmation
        $request->validate([
            'confirmation' => 'required|in:DELETE_MY_DATA',
        ], [
            'confirmation.in' => 'You must type DELETE_MY_DATA to confirm data deletion.',
        ]);

        // Delete the user's posts (and cascade to comments, approvals, etc.)
        foreach ($user->posts as $post) {
            $post->delete();
        }
        
        // Detach user from any tenants
        $user->tenants()->detach();
        
        // Delete social accounts created by this user (if any exist)
        // Note: in this scaffold social accounts are tied to tenants, not users.
        
        // Finally delete the user record
        $user->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Your personal data has been deleted.']);
        }

        return redirect()->route('login')
            ->with('success', 'Your personal data has been deleted. You have been logged out.');
    }

    /**
     * Show data retention policies.
     */
    public function retention(Request $request)
    {
        $policies = [
            'posts' => [
                'retention_period' => '7 years',
                'reason' => 'Business records and compliance requirements',
                'auto_delete' => false,
            ],
            'comments' => [
                'retention_period' => '3 years',
                'reason' => 'User engagement and moderation',
                'auto_delete' => true,
            ],
            'approvals' => [
                'retention_period' => '5 years',
                'reason' => 'Workflow and audit trail',
                'auto_delete' => false,
            ],
            'engagements' => [
                'retention_period' => '2 years',
                'reason' => 'Analytics and reporting',
                'auto_delete' => true,
            ],
            'mentions' => [
                'retention_period' => '1 year',
                'reason' => 'Brand monitoring and response',
                'auto_delete' => true,
            ],
        ];

        if ($request->wantsJson()) {
            return response()->json($policies);
        }

        return view('compliance.retention', compact('policies'));
    }
}