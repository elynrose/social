<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\ScheduledPost;
use App\Models\Campaign;
use Carbon\Carbon;
use App\Jobs\PublishPostJob;

class SchedulerController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $tenantIds = $user->tenants->pluck('id');
        
        $scheduledPosts = ScheduledPost::with(['post.socialAccount', 'post.campaign'])
            ->whereHas('post', function ($query) use ($tenantIds) {
                $query->whereIn('tenant_id', $tenantIds);
            })
            ->orderBy('publish_at')
            ->paginate(20);

        if ($request->wantsJson()) {
            return response()->json($scheduledPosts);
        }

        return view('scheduler.index', compact('scheduledPosts'));
    }

    public function create(Request $request)
    {
        $user = $request->user();
        $tenantIds = $user->tenants->pluck('id');
        
        $posts = Post::whereIn('tenant_id', $tenantIds)
            ->where('status', 'draft')
            ->orderBy('created_at', 'desc')
            ->get();
            
        $campaigns = Campaign::whereIn('tenant_id', $tenantIds)
            ->orderBy('name')
            ->get();

        $timeZones = [
            'UTC' => 'UTC',
            'America/New_York' => 'Eastern Time',
            'America/Chicago' => 'Central Time',
            'America/Denver' => 'Mountain Time',
            'America/Los_Angeles' => 'Pacific Time',
            'Europe/London' => 'London',
            'Europe/Paris' => 'Paris',
            'Asia/Tokyo' => 'Tokyo',
            'Asia/Shanghai' => 'Shanghai',
        ];

        if ($request->wantsJson()) {
            return response()->json(compact('posts', 'campaigns', 'timeZones'));
        }

        return view('scheduler.create', compact('posts', 'campaigns', 'timeZones'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'post_id' => 'required|exists:posts,id',
            'publish_at' => 'required|date|after:now',
            'time_zone' => 'required|string',
        ]);

        $user = $request->user();
        $tenantIds = $user->tenants->pluck('id');
        
        // Ensure user can only schedule posts for their tenants
        $post = Post::whereIn('tenant_id', $tenantIds)
            ->findOrFail($request->post_id);

        $scheduledPost = ScheduledPost::create([
            'post_id' => $post->id,
            'publish_at' => Carbon::parse($request->publish_at),
            'time_zone' => $request->time_zone,
            'status' => 'scheduled',
        ]);

        $post->update(['status' => 'scheduled']);

        // Dispatch job to publish at scheduled time
        PublishPostJob::dispatch($post)->delay($scheduledPost->publish_at);

        if ($request->wantsJson()) {
            return response()->json($scheduledPost, 201);
        }

        return redirect()->route('scheduler.index')
            ->with('success', 'Post scheduled successfully!');
    }

    public function edit(ScheduledPost $scheduledPost)
    {
        $user = auth()->user();
        $tenantIds = $user->tenants->pluck('id');
        
        // Ensure user can only edit scheduled posts for their tenants
        if (!in_array($scheduledPost->post->tenant_id, $tenantIds->toArray())) {
            abort(403, 'Unauthorized action.');
        }

        $timeZones = [
            'UTC' => 'UTC',
            'America/New_York' => 'Eastern Time',
            'America/Chicago' => 'Central Time',
            'America/Denver' => 'Mountain Time',
            'America/Los_Angeles' => 'Pacific Time',
            'Europe/London' => 'London',
            'Europe/Paris' => 'Paris',
            'Asia/Tokyo' => 'Tokyo',
            'Asia/Shanghai' => 'Shanghai',
        ];

        $platforms = [
            'facebook' => 'Facebook',
            'twitter' => 'Twitter',
            'linkedin' => 'LinkedIn',
            'instagram' => 'Instagram',
            'youtube' => 'YouTube'
        ];

        if (request()->wantsJson()) {
            return response()->json(compact('scheduledPost', 'timeZones', 'platforms'));
        }

        return view('scheduler.edit', compact('scheduledPost', 'timeZones', 'platforms'));
    }

    public function update(Request $request, ScheduledPost $scheduledPost)
    {
        $user = auth()->user();
        $tenantIds = $user->tenants->pluck('id');
        
        // Ensure user can only update scheduled posts for their tenants
        if (!in_array($scheduledPost->post->tenant_id, $tenantIds->toArray())) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'publish_at' => 'required|date|after:now',
            'time_zone' => 'required|string',
            'platforms' => 'required|array|min:1',
            'platforms.*' => 'string|in:facebook,twitter,linkedin,instagram,youtube',
        ]);

        $scheduledPost->update([
            'publish_at' => Carbon::parse($request->publish_at),
            'time_zone' => $request->time_zone,
            'platforms' => $request->platforms,
        ]);

        if ($request->wantsJson()) {
            return response()->json($scheduledPost);
        }

        return redirect()->route('scheduler.index')
            ->with('success', 'Scheduled post updated successfully.');
    }

    public function destroy(ScheduledPost $scheduledPost)
    {
        $user = auth()->user();
        $tenantIds = $user->tenants->pluck('id');
        
        // Ensure user can only delete scheduled posts for their tenants
        if (!in_array($scheduledPost->post->tenant_id, $tenantIds->toArray())) {
            abort(403, 'Unauthorized action.');
        }

        $scheduledPost->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Scheduled post deleted successfully.']);
        }

        return redirect()->route('scheduler.index')
            ->with('success', 'Scheduled post deleted successfully.');
    }

    public function schedule(Request $request, Post $post)
    {
        $validated = $request->validate([
            'publish_at' => 'required|date',
            'time_zone' => 'required|string',
        ]);

        $schedule = $post->schedule;
        if (!$schedule) {
            $schedule = new ScheduledPost();
            $schedule->post_id = $post->id;
        }

        $schedule->publish_at = Carbon::parse($validated['publish_at']);
        $schedule->time_zone = $validated['time_zone'];
        $schedule->save();

        $post->status = 'scheduled';
        $post->save();

        // Dispatch a job to publish the post at the scheduled time.  The
        // delay is calculated relative to now.  The job will run via
        // Laravel's queue system (e.g., Redis/Horizon).  Ensure that your
        // queue worker is running for delayed jobs to execute.
        PublishPostJob::dispatch($post)->delay($schedule->publish_at);

        return response()->json($schedule);
    }
}