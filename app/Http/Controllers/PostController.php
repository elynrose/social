<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Campaign;
use App\Models\SocialAccount;
use App\Jobs\GenerateAltTextJob;
use App\Jobs\GenerateCaptionsJob;
use App\Jobs\PublishPostJob;
use App\Services\AIService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\ScheduledPost;

class PostController extends Controller
{
    protected $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function index(Request $request)
    {
        // Check if currentTenant is bound
        if (!app()->bound('currentTenant')) {
            return redirect()->route('login')->with('error', 'Please log in to access posts.');
        }
        
        $query = Post::with(['user', 'socialAccount', 'campaign'])
            ->where('tenant_id', app('currentTenant')->id);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('platform')) {
            $query->whereHas('socialAccount', function ($q) use ($request) {
                $q->where('platform', $request->platform);
            });
        }

        if ($request->filled('search')) {
            $query->where('content', 'like', '%' . $request->search . '%');
        }

        $posts = $query->orderBy('created_at', 'desc')->paginate(15);

        if ($request->wantsJson()) {
            return response()->json($posts);
        }

        return view('posts.index', compact('posts'));
    }

    public function create()
    {
        $campaigns = Campaign::where('tenant_id', app('currentTenant')->id)->get();
        $socialAccounts = SocialAccount::where('tenant_id', app('currentTenant')->id)->get();

        return view('posts.create', compact('campaigns', 'socialAccounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:5000',
            'media' => 'nullable|file|mimes:jpg,jpeg,png,gif,mp4,mov|max:10240', // 10MB max
            'social_account_id' => 'required|exists:social_accounts,id',
            'campaign_id' => 'nullable|exists:campaigns,id',
            'scheduled_at' => 'nullable|date|after:now',
            'requires_approval' => 'boolean',
            'alt_text' => 'nullable|string|max:500',
        ]);

        try {
            $mediaPath = null;
            if ($request->hasFile('media')) {
                $mediaPath = $this->handleMediaUpload($request->file('media'));
            }

            $post = Post::create([
                'tenant_id' => app('currentTenant')->id,
                'user_id' => auth()->id(),
                'social_account_id' => $request->social_account_id,
                'campaign_id' => $request->campaign_id,
                'content' => $request->content,
                'media_path' => $mediaPath,
                'alt_text' => $request->alt_text,
                'status' => $request->input('status', 'draft'),
            ]);

            // Generate AI content if media is uploaded
            if ($mediaPath && !$request->alt_text) {
                GenerateAltTextJob::dispatch($post->id);
            }

            // Generate captions if requested
            if ($request->boolean('generate_captions')) {
                GenerateCaptionsJob::dispatch($post->id, $request->platform, $request->tone ?? 'professional');
            }

            // Handle scheduling
            if ($request->scheduled_at) {
                ScheduledPost::create([
                    'post_id' => $post->id,
                    'publish_at' => $request->scheduled_at,
                    'time_zone' => config('app.timezone'),
                    'status' => 'scheduled',
                ]);
                $post->update(['status' => 'scheduled']);
            }

            // Handle approval workflow
            if ($request->boolean('requires_approval')) {
                $post->approvals()->create([
                    'user_id' => auth()->id(),
                    'status' => 'pending',
                    'step' => 1,
                ]);
                $post->update(['status' => 'pending_approval']);
            }

            // Publish immediately if requested
            if ($request->input('publish_type') === 'now' && !$request->scheduled_at) {
                PublishPostJob::dispatch($post);
            }

            return redirect()->route('posts.index')
                ->with('success', 'Post created successfully.');

        } catch (\Exception $e) {
            Log::error('Post creation failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return back()->withInput()
                ->with('error', 'Failed to create post. Please try again.');
        }
    }

    public function show(Post $post)
    {
        $this->authorize('view', $post);

        $post->load(['user', 'socialAccount', 'campaign', 'approvals', 'comments.user']);

        if (request()->wantsJson()) {
            return response()->json($post);
        }

        return view('posts.show', compact('post'));
    }

    public function edit(Post $post)
    {
        $this->authorize('update', $post);

        $campaigns = Campaign::where('tenant_id', app('currentTenant')->id)->get();
        $socialAccounts = SocialAccount::where('tenant_id', app('currentTenant')->id)->get();

        return view('posts.edit', compact('post', 'campaigns', 'socialAccounts'));
    }

    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post);

        $request->validate([
            'content' => 'required|string|max:5000',
            'media' => 'nullable|file|mimes:jpg,jpeg,png,gif,mp4,mov|max:10240',
            'alt_text' => 'nullable|string|max:500',
            'status' => 'nullable|string|in:draft,scheduled,published,failed',
        ]);

        try {
            $data = [
                'content' => $request->content,
                'alt_text' => $request->alt_text,
            ];

            // Handle media upload
            if ($request->hasFile('media')) {
                // Delete old media
                if ($post->media_path) {
                    Storage::disk('public')->delete($post->media_path);
                }
                $data['media_path'] = $this->handleMediaUpload($request->file('media'));
            }

            $post->update($data);

            // Generate alt text if media is new and no alt text provided
            if ($request->hasFile('media') && !$request->alt_text) {
                GenerateAltTextJob::dispatch($post->id);
            }

            return redirect()->route('posts.index')
                ->with('success', 'Post updated successfully.');

        } catch (\Exception $e) {
            Log::error('Post update failed', [
                'error' => $e->getMessage(),
                'post_id' => $post->id,
            ]);

            return back()->withInput()
                ->with('error', 'Failed to update post. Please try again.');
        }
    }

    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);

        try {
            // Delete associated media
            if ($post->media_path) {
                Storage::disk('public')->delete($post->media_path);
            }

            $post->delete();

            return response()->json([
                'success' => true,
                'message' => 'Post deleted successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Post deletion failed', [
                'error' => $e->getMessage(),
                'post_id' => $post->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete post.',
            ], 500);
        }
    }

    public function duplicate(Post $post)
    {
        $this->authorize('create', Post::class);

        try {
            $newPost = $post->replicate();
            $newPost->status = 'draft';
            $newPost->external_id = null; // Reset external ID
            $newPost->save();

            return response()->json([
                'success' => true,
                'message' => 'Post duplicated successfully.',
                'post_id' => $newPost->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Post duplication failed', [
                'error' => $e->getMessage(),
                'post_id' => $post->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to duplicate post.',
            ], 500);
        }
    }

    public function publish(Post $post)
    {
        $this->authorize('update', $post);

        try {
            PublishPostJob::dispatch($post);

            return response()->json([
                'success' => true,
                'message' => 'Post queued for publishing.',
            ]);

        } catch (\Exception $e) {
            Log::error('Post publishing failed', [
                'error' => $e->getMessage(),
                'post_id' => $post->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to publish post.',
            ], 500);
        }
    }

    protected function handleMediaUpload($file)
    {
        $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('posts', $filename, 'public');
        
        return $path;
    }

    public function analytics(Post $post)
    {
        $this->authorize('view', $post);

        $analytics = $post->engagements()
            ->selectRaw('platform, SUM(likes) as total_likes, SUM(comments) as total_comments, SUM(shares) as total_shares')
            ->groupBy('platform')
            ->get();

        return response()->json([
            'analytics' => $analytics,
            'total_engagement' => $analytics->sum('total_likes') + $analytics->sum('total_comments') + $analytics->sum('total_shares'),
        ]);
    }
}