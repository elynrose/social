<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ScheduledPost;
use App\Models\Post;
use App\Models\Campaign;
use Illuminate\Support\Str;

class CalendarController extends Controller
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

        return view('calendar.index', compact('scheduledPosts'));
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

        if ($request->wantsJson()) {
            return response()->json(compact('posts', 'campaigns'));
        }

        return view('calendar.create', compact('posts', 'campaigns'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'post_id' => 'required|exists:posts,id',
            'publish_at' => 'required|date|after:now',
            'platforms' => 'required|array|min:1',
            'platforms.*' => 'string|in:facebook,twitter,linkedin,instagram,youtube',
        ]);

        $user = $request->user();
        $tenantIds = $user->tenants->pluck('id');
        
        // Ensure user can only schedule posts for their tenants
        $post = Post::whereIn('tenant_id', $tenantIds)
            ->findOrFail($request->post_id);

        $scheduledPost = ScheduledPost::create([
            'post_id' => $post->id,
            'publish_at' => $request->publish_at,
            'platforms' => $request->platforms,
            'status' => 'scheduled',
        ]);

        if ($request->wantsJson()) {
            return response()->json($scheduledPost, 201);
        }

        return redirect()->route('calendar.index')
            ->with('success', 'Post scheduled successfully.');
    }

    public function edit(ScheduledPost $scheduledPost)
    {
        $user = auth()->user();
        $tenantIds = $user->tenants->pluck('id')->toArray();
        
        // Ensure user can only edit scheduled posts for their tenants
        if (empty($tenantIds) || !in_array($scheduledPost->post->tenant_id, $tenantIds)) {
            abort(403, 'Unauthorized action.');
        }

        $platforms = [
            'facebook' => 'Facebook',
            'twitter' => 'Twitter',
            'linkedin' => 'LinkedIn',
            'instagram' => 'Instagram',
            'youtube' => 'YouTube'
        ];

        if (request()->wantsJson()) {
            return response()->json(compact('scheduledPost', 'platforms'));
        }

        return view('calendar.edit', compact('scheduledPost', 'platforms'));
    }

    public function update(Request $request, ScheduledPost $scheduledPost)
    {
        $user = auth()->user();
        $tenantIds = $user->tenants->pluck('id')->toArray();
        
        // Ensure user can only update scheduled posts for their tenants
        if (empty($tenantIds) || !in_array($scheduledPost->post->tenant_id, $tenantIds)) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'publish_at' => 'required|date|after:now',
        ]);

        $scheduledPost->update([
            'publish_at' => $request->publish_at,
        ]);

        if ($request->wantsJson()) {
            return response()->json($scheduledPost);
        }

        return redirect()->route('calendar.index')
            ->with('success', 'Scheduled post updated successfully.');
    }

    public function destroy(ScheduledPost $scheduledPost)
    {
        $user = auth()->user();
        $tenantIds = $user->tenants->pluck('id')->toArray();
        
        // Ensure user can only delete scheduled posts for their tenants
        if (empty($tenantIds) || !in_array($scheduledPost->post->tenant_id, $tenantIds)) {
            abort(403, 'Unauthorized action.');
        }

        $scheduledPost->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Scheduled post deleted successfully.']);
        }

        return redirect()->route('calendar.index')
            ->with('success', 'Scheduled post deleted successfully.');
    }

    public function events(Request $request)
    {
        $user = auth()->user();
        $events = [];
        $schedules = ScheduledPost::with(['post' => function ($query) use ($user) {
            $query->whereIn('tenant_id', $user->tenants->pluck('id'));
        }])->get();
        
        foreach ($schedules as $schedule) {
            if (!$schedule->post) continue;
            
            $events[] = [
                'id' => $schedule->id,
                'title' => Str::limit($schedule->post->content, 30),
                'start' => $schedule->publish_at->toAtomString(),
                'url' => route('calendar.edit', $schedule->id),
                'backgroundColor' => $this->getEventColor($schedule->status ?? 'scheduled'),
                'borderColor' => $this->getEventColor($schedule->status ?? 'scheduled'),
                'status' => $schedule->status ?? 'scheduled',
                'extendedProps' => [
                    'status' => $schedule->status ?? 'scheduled',
                    'post_content' => $schedule->post->content,
                    'campaign' => $schedule->post->campaign?->name,
                    'publish_time' => $schedule->publish_at->format('g:i A'),
                ]
            ];
        }
        
        return response()->json($events);
    }

    private function getEventColor($status)
    {
        return match($status) {
            'scheduled' => '#007bff',
            'published' => '#28a745',
            'failed' => '#dc3545',
            'cancelled' => '#6c757d',
            default => '#6c757d',
        };
    }
}