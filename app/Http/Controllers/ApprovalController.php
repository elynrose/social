<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Approval;
use App\Models\Tenant;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ApprovalStatusChangedNotification;

class ApprovalController extends Controller
{
    /**
     * Display a listing of approvals for the current user.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Get approvals where the current user is the approver
        $pendingApprovals = Approval::with(['post.user', 'post.campaign'])
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->orderBy('step')
            ->get();
            
        // Get all approvals for posts in the current tenant
        $allApprovals = Approval::with(['post.user', 'post.campaign', 'user'])
            ->whereHas('post', function ($query) {
                $query->where('tenant_id', app('currentTenant')->id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        if ($request->wantsJson()) {
            return response()->json([
                'pending_approvals' => $pendingApprovals,
                'all_approvals' => $allApprovals
            ]);
        }
        
        return view('approval', compact('pendingApprovals', 'allApprovals'));
    }

    public function requestApproval(Request $request, Post $post)
    {
        $validated = $request->validate([
            'approver_ids' => 'required|array',
            'approver_ids.*' => 'exists:users,id',
        ]);
        // Create a multi‑step approval workflow by assigning sequential
        // step numbers to each approver.  Lower numbers are reviewed first.
        $step = 1;
        foreach ($validated['approver_ids'] as $approverId) {
            Approval::create([
                'post_id' => $post->id,
                'user_id' => $approverId,
                'step' => $step++,
                'status' => 'pending',
            ]);
        }
        // Notify the first approver that a post is awaiting their review
        $firstApproverId = $validated['approver_ids'][0];
        $firstApproval = Approval::where('post_id', $post->id)->where('user_id', $firstApproverId)->first();
        if ($firstApproval) {
            $tenant = Tenant::find($post->tenant_id);
            $firstUser = $tenant->users()->find($firstApproverId);
            if ($firstUser) {
                Notification::send($firstUser, new ApprovalStatusChangedNotification($firstApproval));
            }
        }
        return response()->json(['message' => 'Approval workflow created.']);
    }

    /**
     * Approve a pending approval record.
     */
    public function approve(Request $request, Approval $approval)
    {
        // Update this approval record to approved
        $approval->status = 'approved';
        $approval->save();
        $post = $approval->post;
        // If there is a next step approval pending, notify that approver
        $nextApproval = Approval::where('post_id', $post->id)
            ->where('step', '>', $approval->step)
            ->orderBy('step')
            ->first();
        if ($nextApproval) {
            $tenant = Tenant::find($post->tenant_id);
            $nextUser = $tenant->users()->find($nextApproval->user_id);
            if ($nextUser) {
                Notification::send($nextUser, new ApprovalStatusChangedNotification($nextApproval));
            }
        } else {
            // All approvals completed; update the post status
            $post->status = 'approved';
            $post->save();
        }
        // Notify team members about status change
        $tenant = Tenant::find($post->tenant_id);
        Notification::send($tenant->users, new ApprovalStatusChangedNotification($approval));
        
        if ($request->wantsJson()) {
            return response()->json(['message' => 'Approval updated.']);
        }
        
        return redirect()->route('approval.index')->with('success', 'Post approved successfully!');
    }

    /**
     * Reject a pending approval record.
     */
    public function reject(Request $request, Approval $approval)
    {
        $approval->status = 'rejected';
        $approval->comments = $request->input('comments');
        $approval->save();
        $post = $approval->post;
        // When any approver rejects the post, mark the post as rejected
        $post->status = 'rejected';
        $post->save();
        // Notify team members about the rejection
        $tenant = Tenant::find($post->tenant_id);
        Notification::send($tenant->users, new ApprovalStatusChangedNotification($approval));
        
        if ($request->wantsJson()) {
            return response()->json(['message' => 'Approval rejected.']);
        }
        
        return redirect()->route('approval.index')->with('success', 'Post rejected successfully!');
    }
}