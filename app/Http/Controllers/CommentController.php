<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Tenant;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NewCommentNotification;

class CommentController extends Controller
{
    /**
     * Display a listing of all comments for the current tenant.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $tenantIds = $user->tenants->pluck('id');
        
        $comments = Comment::with(['post', 'user', 'parent'])
            ->whereIn('tenant_id', $tenantIds)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        if ($request->wantsJson()) {
            return response()->json($comments);
        }

        return view('comments.index', compact('comments'));
    }

    /**
     * Show the form for creating a new comment.
     */
    public function create(Request $request)
    {
        $user = $request->user();
        $tenantIds = $user->tenants->pluck('id');
        
        $posts = Post::whereIn('tenant_id', $tenantIds)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($request->wantsJson()) {
            return response()->json(compact('posts'));
        }

        return view('comments.create', compact('posts'));
    }

    /**
     * Display a listing of comments for the specified post.
     */
    public function postComments(Request $request, Post $post)
    {
        $this->authorize('view', $post);
        $comments = $post->comments()->with('user')->whereNull('parent_id')->latest()->get();
        
        if ($request->wantsJson()) {
            return response()->json($comments);
        }

        return view('comments.post', compact('comments', 'post'));
    }

    /**
     * Store a newly created comment in storage.
     */
    public function store(Request $request, Post $post = null)
    {
        if ($post) {
            $this->authorize('comment', $post);
        } else {
            $request->validate([
                'post_id' => 'required|exists:posts,id',
            ]);
            $post = Post::findOrFail($request->post_id);
            $this->authorize('comment', $post);
        }

        $data = $request->validate([
            'content' => ['required', 'string', 'max:1000'],
            'parent_id' => ['nullable', 'exists:comments,id'],
        ]);

        $comment = $post->comments()->create([
            'tenant_id' => $post->tenant_id,
            'user_id' => $request->user()->id,
            'content' => $data['content'],
            'parent_id' => $data['parent_id'] ?? null,
        ]);

        // Notify team members of new comment
        $tenant = Tenant::find($post->tenant_id);
        $users = $tenant->users;
        Notification::send($users, new NewCommentNotification($comment));

        if ($request->wantsJson()) {
            return response()->json($comment, 201);
        }

        return redirect()->route('comments.index')
            ->with('success', 'Comment added successfully.');
    }

    /**
     * Show the form for editing the specified comment.
     */
    public function edit(Comment $comment)
    {
        $this->authorize('update', $comment);

        if (request()->wantsJson()) {
            return response()->json($comment);
        }

        return view('comments.edit', compact('comment'));
    }

    /**
     * Update the specified comment in storage.
     */
    public function update(Request $request, Comment $comment)
    {
        $this->authorize('update', $comment);

        $data = $request->validate([
            'content' => ['required', 'string', 'max:1000'],
        ]);

        $comment->update([
            'content' => $data['content'],
        ]);

        if ($request->wantsJson()) {
            return response()->json($comment);
        }

        return redirect()->route('comments.index')
            ->with('success', 'Comment updated successfully.');
    }

    /**
     * Remove the specified comment.
     */
    public function destroy(Request $request, Comment $comment)
    {
        $this->authorize('delete', $comment);
        $comment->delete();

        if ($request->wantsJson()) {
            return response()->noContent();
        }

        return redirect()->route('comments.index')
            ->with('success', 'Comment deleted successfully.');
    }
}