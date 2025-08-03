@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Post Details</h4>
                    <div class="btn-group">
                        <a href="{{ route('posts.edit', $post) }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="deletePost({{ $post->id }})">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <!-- Post Content -->
                            <div class="mb-4">
                                <h5>Content</h5>
                                <div class="border rounded p-3 bg-light">
                                    {!! $post->content !!}
                                </div>
                            </div>

                            <!-- Media -->
                            @if($post->media_path)
                                <div class="mb-4">
                                    <h5>Media</h5>
                                    @if(Str::endsWith($post->media_path, ['.jpg', '.jpeg', '.png', '.gif']))
                                        <img src="{{ Storage::url($post->media_path) }}" class="img-fluid rounded" alt="{{ $post->alt_text }}">
                                    @elseif(Str::endsWith($post->media_path, ['.mp4', '.mov']))
                                        <video controls class="img-fluid rounded">
                                            <source src="{{ Storage::url($post->media_path) }}" type="video/mp4">
                                            Your browser does not support the video tag.
                                        </video>
                                    @endif
                                    @if($post->alt_text)
                                        <small class="text-muted mt-2 d-block">Alt Text: {{ $post->alt_text }}</small>
                                    @endif
                                </div>
                            @endif

                            <!-- Comments -->
                            <div class="mb-4">
                                <h5>Comments</h5>
                                <div id="comments-section">
                                    @foreach($post->comments as $comment)
                                        <div class="border rounded p-3 mb-2">
                                            <div class="d-flex justify-content-between">
                                                <strong>{{ $comment->user->name }}</strong>
                                                <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                                            </div>
                                            <p class="mb-0 mt-2">{{ $comment->content }}</p>
                                        </div>
                                    @endforeach
                                </div>
                                
                                <!-- Add Comment -->
                                <form id="comment-form" class="mt-3">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="comment-content" placeholder="Add a comment...">
                                        <button type="submit" class="btn btn-primary">Post</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <!-- Post Status -->
                            <div class="mb-4">
                                <h6>Status</h6>
                                <span class="badge bg-{{ $post->status === 'published' ? 'success' : ($post->status === 'scheduled' ? 'primary' : 'secondary') }}">
                                    {{ ucfirst($post->status) }}
                                </span>
                            </div>

                            <!-- Post Information -->
                            <div class="mb-4">
                                <h6>Information</h6>
                                <ul class="list-unstyled">
                                    <li><strong>Created:</strong> {{ $post->created_at->format('M j, Y g:i A') }}</li>
                                    <li><strong>Author:</strong> {{ $post->user->name }}</li>
                                    @if($post->socialAccount)
                                        <li><strong>Platform:</strong> {{ ucfirst($post->socialAccount->platform) }}</li>
                                    @endif
                                    @if($post->campaign)
                                        <li><strong>Campaign:</strong> {{ $post->campaign->name }}</li>
                                    @endif
                                    @if($post->external_id)
                                        <li><strong>External ID:</strong> {{ $post->external_id }}</li>
                                    @endif
                                </ul>
                            </div>

                            <!-- Approvals -->
                            @if($post->approvals->count() > 0)
                                <div class="mb-4">
                                    <h6>Approvals</h6>
                                    @foreach($post->approvals as $approval)
                                        <div class="border rounded p-2 mb-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span>{{ $approval->user->name }}</span>
                                                <span class="badge bg-{{ $approval->status === 'approved' ? 'success' : ($approval->status === 'rejected' ? 'danger' : 'warning') }}">
                                                    {{ ucfirst($approval->status) }}
                                                </span>
                                            </div>
                                            @if($approval->comment)
                                                <small class="text-muted">{{ $approval->comment }}</small>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Actions -->
                            <div class="mb-4">
                                <h6>Actions</h6>
                                <div class="d-grid gap-2">
                                    @if($post->status === 'draft')
                                        <button type="button" class="btn btn-success btn-sm" onclick="publishPost({{ $post->id }})">
                                            <i class="fas fa-paper-plane"></i> Publish Now
                                        </button>
                                    @endif
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="duplicatePost({{ $post->id }})">
                                        <i class="fas fa-copy"></i> Duplicate
                                    </button>
                                    <button type="button" class="btn btn-outline-info btn-sm" onclick="viewAnalytics({{ $post->id }})">
                                        <i class="fas fa-chart-bar"></i> View Analytics
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Analytics Modal -->
<div class="modal fade" id="analyticsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Post Analytics</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="analytics-content">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function deletePost(postId) {
    if (confirm('Are you sure you want to delete this post?')) {
        fetch(`/posts/${postId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '/posts';
            } else {
                alert('Failed to delete post');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to delete post');
        });
    }
}

function publishPost(postId) {
    fetch(`/posts/${postId}/publish`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Failed to publish post');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to publish post');
    });
}

function duplicatePost(postId) {
    fetch(`/posts/${postId}/duplicate`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = `/posts/${data.post_id}/edit`;
        } else {
            alert('Failed to duplicate post');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to duplicate post');
    });
}

function viewAnalytics(postId) {
    const modal = new bootstrap.Modal(document.getElementById('analyticsModal'));
    modal.show();
    
    fetch(`/posts/${postId}/analytics`)
        .then(response => response.json())
        .then(data => {
            const content = document.getElementById('analytics-content');
            if (data.analytics && data.analytics.length > 0) {
                let html = '<div class="table-responsive"><table class="table">';
                html += '<thead><tr><th>Platform</th><th>Likes</th><th>Comments</th><th>Shares</th></tr></thead><tbody>';
                
                data.analytics.forEach(metric => {
                    html += `<tr>
                        <td>${metric.platform}</td>
                        <td>${metric.total_likes}</td>
                        <td>${metric.total_comments}</td>
                        <td>${metric.total_shares}</td>
                    </tr>`;
                });
                
                html += '</tbody></table></div>';
                html += `<p class="text-center"><strong>Total Engagement: ${data.total_engagement}</strong></p>`;
                content.innerHTML = html;
            } else {
                content.innerHTML = '<p class="text-center text-muted">No analytics data available yet.</p>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('analytics-content').innerHTML = '<p class="text-center text-danger">Failed to load analytics.</p>';
        });
}

// Comment form submission
document.getElementById('comment-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const content = document.getElementById('comment-content').value;
    if (!content.trim()) return;
    
    fetch(`/posts/{{ $post->id }}/comments`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ content })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Failed to add comment');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to add comment');
    });
});
</script>
@endpush
@endsection 