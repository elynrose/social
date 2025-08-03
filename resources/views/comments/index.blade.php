@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-comments"></i> Comments Management
                    </h5>
                    <div class="btn-group">
                        <a href="{{ route('comments.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add Comment
                        </a>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="refreshComments()">
                            <i class="fas fa-sync"></i> Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <!-- Filter Options -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Filter Comments</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label for="filter_post" class="form-label">Filter by Post</label>
                                            <select id="filter_post" class="form-select" onchange="filterComments()">
                                                <option value="">All Posts</option>
                                                @foreach($comments as $comment)
                                                    @if($comment->post)
                                                        <option value="{{ $comment->post->id }}">
                                                            {{ Str::limit($comment->post->content, 50) }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="filter_user" class="form-label">Filter by User</label>
                                            <select id="filter_user" class="form-select" onchange="filterComments()">
                                                <option value="">All Users</option>
                                                @foreach($comments as $comment)
                                                    @if($comment->user)
                                                        <option value="{{ $comment->user->id }}">
                                                            {{ $comment->user->name }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="filter_date" class="form-label">Filter by Date</label>
                                            <select id="filter_date" class="form-select" onchange="filterComments()">
                                                <option value="">All Dates</option>
                                                <option value="today">Today</option>
                                                <option value="week">This Week</option>
                                                <option value="month">This Month</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">&nbsp;</label>
                                            <div>
                                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearFilters()">
                                                    <i class="fas fa-times"></i> Clear Filters
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Comments List -->
                    <div class="row">
                        <div class="col-md-12">
                            @if($comments->count() > 0)
                                <div class="comments-list">
                                    @foreach($comments as $comment)
                                        <div class="card mb-3 comment-item" 
                                             data-post-id="{{ $comment->post_id ?? '' }}"
                                             data-user-id="{{ $comment->user_id ?? '' }}"
                                             data-date="{{ $comment->created_at->format('Y-m-d') }}">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar me-3">
                                                            <i class="fas fa-user-circle fa-2x text-primary"></i>
                                                        </div>
                                                        <div>
                                                            <strong>{{ $comment->user->name ?? 'Unknown User' }}</strong>
                                                            <br><small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                                                        </div>
                                                    </div>
                                                    <div class="btn-group">
                                                        @can('update', $comment)
                                                            <a href="{{ route('comments.edit', $comment) }}" 
                                                               class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                        @endcan
                                                        @can('delete', $comment)
                                                            <form action="{{ route('comments.destroy', $comment) }}" 
                                                                  method="POST" 
                                                                  class="d-inline"
                                                                  onsubmit="return confirm('Are you sure you want to delete this comment?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        @endcan
                                                    </div>
                                                </div>
                                                
                                                <div class="comment-content mb-3">
                                                    <p class="mb-0">{{ $comment->content }}</p>
                                                </div>
                                                
                                                @if($comment->post)
                                                    <div class="comment-post-info">
                                                        <small class="text-muted">
                                                            <i class="fas fa-file-alt"></i> 
                                                            Comment on: 
                                                            <a href="{{ route('posts.show', $comment->post) }}">
                                                                {{ Str::limit($comment->post->content, 100) }}
                                                            </a>
                                                        </small>
                                                    </div>
                                                @endif
                                                
                                                @if($comment->parent)
                                                    <div class="comment-reply-info mt-2">
                                                        <small class="text-muted">
                                                            <i class="fas fa-reply"></i> 
                                                            Reply to: {{ Str::limit($comment->parent->content, 50) }}
                                                        </small>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                
                                <!-- Pagination -->
                                <div class="d-flex justify-content-center mt-4">
                                    {{ $comments->links() }}
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No Comments</h5>
                                    <p class="text-muted">No comments have been made yet.</p>
                                    <a href="{{ route('comments.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Add Your First Comment
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function filterComments() {
    const postFilter = document.getElementById('filter_post').value;
    const userFilter = document.getElementById('filter_user').value;
    const dateFilter = document.getElementById('filter_date').value;
    
    const comments = document.querySelectorAll('.comment-item');
    const today = new Date().toISOString().split('T')[0];
    const weekAgo = new Date(Date.now() - 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
    const monthAgo = new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
    
    comments.forEach(comment => {
        let show = true;
        
        // Filter by post
        if (postFilter && comment.dataset.postId !== postFilter) {
            show = false;
        }
        
        // Filter by user
        if (userFilter && comment.dataset.userId !== userFilter) {
            show = false;
        }
        
        // Filter by date
        if (dateFilter) {
            const commentDate = comment.dataset.date;
            switch(dateFilter) {
                case 'today':
                    if (commentDate !== today) show = false;
                    break;
                case 'week':
                    if (commentDate < weekAgo) show = false;
                    break;
                case 'month':
                    if (commentDate < monthAgo) show = false;
                    break;
            }
        }
        
        comment.style.display = show ? 'block' : 'none';
    });
}

function clearFilters() {
    document.getElementById('filter_post').value = '';
    document.getElementById('filter_user').value = '';
    document.getElementById('filter_date').value = '';
    
    const comments = document.querySelectorAll('.comment-item');
    comments.forEach(comment => {
        comment.style.display = 'block';
    });
}

function refreshComments() {
    location.reload();
}
</script>
@endpush
@endsection 