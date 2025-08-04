@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Posts</h1>
        <a href="{{ route('posts.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> New Post
        </a>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('posts.index') }}" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search posts..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="platform" class="form-select">
                        <option value="">All Platforms</option>
                        <option value="facebook" {{ request('platform') === 'facebook' ? 'selected' : '' }}>Facebook</option>
                        <option value="twitter" {{ request('platform') === 'twitter' ? 'selected' : '' }}>Twitter</option>
                        <option value="linkedin" {{ request('platform') === 'linkedin' ? 'selected' : '' }}>LinkedIn</option>
                        <option value="instagram" {{ request('platform') === 'instagram' ? 'selected' : '' }}>Instagram</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('posts.index') }}" class="btn btn-outline-secondary w-100">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            @if($posts->count() > 0)
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Content</th>
                        <th>Platform</th>
                        <th>Status</th>
                        <th>Campaign</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($posts as $post)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                @if($post->media_path)
                                <div class="me-3">
                                    <img src="{{ Storage::url($post->media_path) }}" alt="Media" class="rounded" style="width: 40px; height: 40px; object-fit: cover;">
                                </div>
                                @endif
                                <div>
                                    <div class="fw-bold">{{ Str::limit($post->content, 100) }}</div>
                                    @if($post->user)
                                    <small class="text-muted">by {{ $post->user->name }}</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($post->socialAccount)
                            <span class="badge bg-primary">
                                <i class="fab fa-{{ $post->socialAccount->platform }}"></i>
                                {{ ucfirst($post->socialAccount->platform) }}
                            </span>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $statusColors = [
                                    'draft' => 'secondary',
                                    'scheduled' => 'warning',
                                    'published' => 'success',
                                    'failed' => 'danger',
                                    'pending_approval' => 'info'
                                ];
                                $color = $statusColors[$post->status] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $color }}">{{ ucfirst(str_replace('_', ' ', $post->status)) }}</span>
                        </td>
                        <td>
                            @if($post->campaign)
                            <span class="badge bg-info">{{ $post->campaign->name }}</span>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <small>{{ $post->created_at->format('M j, Y g:i A') }}</small>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('posts.show', $post) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('posts.edit', $post) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if($post->status === 'draft')
                                <button type="button" class="btn btn-sm btn-outline-success" onclick="publishPost({{ $post->id }})">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                                @endif
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deletePost({{ $post->id }})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="text-center py-5">
                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No posts found</h5>
                <p class="text-muted">Create your first post to get started.</p>
                <a href="{{ route('posts.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Post
                </a>
            </div>
            @endif
        </div>
    </div>

    @if($posts->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $posts->links() }}
    </div>
    @endif
</div>

<script>
function publishPost(postId) {
    if (confirm('Are you sure you want to publish this post?')) {
        fetch(`/posts/${postId}/publish`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to publish post: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to publish post');
        });
    }
}

function deletePost(postId) {
    if (confirm('Are you sure you want to delete this post? This action cannot be undone.')) {
        fetch(`/posts/${postId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to delete post: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to delete post');
        });
    }
}
</script>
@endsection