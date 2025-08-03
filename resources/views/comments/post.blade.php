@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <!-- Post Details -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4><i class="fas fa-file-alt"></i> Post Details</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h5>{{ $post->title ?? 'Untitled Post' }}</h5>
                            <p class="text-muted mb-2">
                                <i class="fas fa-user"></i> {{ $post->user->name ?? 'Unknown' }} | 
                                <i class="fas fa-calendar"></i> {{ $post->created_at->format('M d, Y H:i') }}
                            </p>
                            <div class="post-content">
                                {!! nl2br(e($post->content)) !!}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6><i class="fas fa-chart-bar"></i> Post Stats</h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Comments:</span>
                                        <strong>{{ $comments->count() }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Likes:</span>
                                        <strong>{{ $post->likes_count ?? 0 }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Shares:</span>
                                        <strong>{{ $post->shares_count ?? 0 }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Status:</span>
                                        <span class="badge bg-{{ $post->status === 'published' ? 'success' : 'warning' }}">
                                            {{ ucfirst($post->status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comments Section -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4><i class="fas fa-comments"></i> Comments ({{ $comments->count() }})</h4>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-primary" onclick="refreshComments()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                        <a href="{{ route('comments.create') }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> Add Comment
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <select class="form-select" id="statusFilter">
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                                <option value="spam">Spam</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="sentimentFilter">
                                <option value="">All Sentiment</option>
                                <option value="positive">Positive</option>
                                <option value="neutral">Neutral</option>
                                <option value="negative">Negative</option>
                                <option value="mixed">Mixed</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="sortFilter">
                                <option value="newest">Newest First</option>
                                <option value="oldest">Oldest First</option>
                                <option value="sentiment">By Sentiment</option>
                                <option value="status">By Status</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control" id="searchFilter" placeholder="Search comments...">
                        </div>
                    </div>

                    <!-- Comments List -->
                    <div id="commentsList">
                        @forelse($comments as $comment)
                            <div class="comment-item card mb-3" data-status="{{ $comment->status }}" data-sentiment="{{ $comment->sentiment }}">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="avatar me-2">
                                                    <i class="fas fa-user-circle fa-2x text-muted"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $comment->user->name ?? 'Anonymous' }}</h6>
                                                    <small class="text-muted">
                                                        {{ $comment->created_at->format('M d, Y H:i') }}
                                                        @if($comment->is_pinned)
                                                            <span class="badge bg-warning ms-1">Pinned</span>
                                                        @endif
                                                        @if($comment->is_featured)
                                                            <span class="badge bg-info ms-1">Featured</span>
                                                        @endif
                                                    </small>
                                                </div>
                                            </div>
                                            <div class="comment-content mb-2">
                                                {!! nl2br(e($comment->content)) !!}
                                            </div>
                                            <div class="comment-meta">
                                                <span class="badge bg-{{ $comment->status === 'approved' ? 'success' : ($comment->status === 'pending' ? 'warning' : 'danger') }}">
                                                    {{ ucfirst($comment->status) }}
                                                </span>
                                                <span class="badge bg-{{ $comment->sentiment === 'positive' ? 'success' : ($comment->sentiment === 'negative' ? 'danger' : 'secondary') }} ms-1">
                                                    {{ ucfirst($comment->sentiment) }}
                                                </span>
                                                <span class="badge bg-secondary ms-1">{{ ucfirst($comment->type) }}</span>
                                            </div>
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="{{ route('comments.edit', $comment->id) }}">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a></li>
                                                <li><a class="dropdown-item" href="#" onclick="viewCommentDetails({{ $comment->id }})">
                                                    <i class="fas fa-eye"></i> View Details
                                                </a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-success" href="#" onclick="approveComment({{ $comment->id }})">
                                                    <i class="fas fa-check"></i> Approve
                                                </a></li>
                                                <li><a class="dropdown-item text-danger" href="#" onclick="rejectComment({{ $comment->id }})">
                                                    <i class="fas fa-times"></i> Reject
                                                </a></li>
                                                <li><a class="dropdown-item text-warning" href="#" onclick="markAsSpam({{ $comment->id }})">
                                                    <i class="fas fa-ban"></i> Mark as Spam
                                                </a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4">
                                <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                                <h5>No comments yet</h5>
                                <p class="text-muted">Be the first to comment on this post!</p>
                                <a href="{{ route('comments.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Add Comment
                                </a>
                            </div>
                        @endforelse
                    </div>

                    <!-- Pagination -->
                    @if($comments->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $comments->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Comment Details Modal -->
<div class="modal fade" id="commentDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Comment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="commentDetailsContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filter functionality
    const statusFilter = document.getElementById('statusFilter');
    const sentimentFilter = document.getElementById('sentimentFilter');
    const sortFilter = document.getElementById('sortFilter');
    const searchFilter = document.getElementById('searchFilter');
    
    function filterComments() {
        const status = statusFilter.value;
        const sentiment = sentimentFilter.value;
        const search = searchFilter.value.toLowerCase();
        
        document.querySelectorAll('.comment-item').forEach(item => {
            const itemStatus = item.dataset.status;
            const itemSentiment = item.dataset.sentiment;
            const content = item.textContent.toLowerCase();
            
            const statusMatch = !status || itemStatus === status;
            const sentimentMatch = !sentiment || itemSentiment === sentiment;
            const searchMatch = !search || content.includes(search);
            
            if (statusMatch && sentimentMatch && searchMatch) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    }
    
    statusFilter.addEventListener('change', filterComments);
    sentimentFilter.addEventListener('change', filterComments);
    searchFilter.addEventListener('input', filterComments);
    
    // Sort functionality
    sortFilter.addEventListener('change', function() {
        const sortBy = this.value;
        const commentsList = document.getElementById('commentsList');
        const comments = Array.from(commentsList.querySelectorAll('.comment-item'));
        
        comments.sort((a, b) => {
            switch(sortBy) {
                case 'newest':
                    return new Date(b.querySelector('small').textContent) - new Date(a.querySelector('small').textContent);
                case 'oldest':
                    return new Date(a.querySelector('small').textContent) - new Date(b.querySelector('small').textContent);
                case 'sentiment':
                    return a.dataset.sentiment.localeCompare(b.dataset.sentiment);
                case 'status':
                    return a.dataset.status.localeCompare(b.dataset.status);
                default:
                    return 0;
            }
        });
        
        comments.forEach(comment => commentsList.appendChild(comment));
    });
});

function refreshComments() {
    location.reload();
}

function viewCommentDetails(commentId) {
    // Implementation for viewing comment details
    const modal = new bootstrap.Modal(document.getElementById('commentDetailsModal'));
    modal.show();
}

function approveComment(commentId) {
    if (confirm('Are you sure you want to approve this comment?')) {
        // Implementation for approving comment
        alert('Comment approved!');
    }
}

function rejectComment(commentId) {
    if (confirm('Are you sure you want to reject this comment?')) {
        // Implementation for rejecting comment
        alert('Comment rejected!');
    }
}

function markAsSpam(commentId) {
    if (confirm('Are you sure you want to mark this comment as spam?')) {
        // Implementation for marking comment as spam
        alert('Comment marked as spam!');
    }
}
</script>
@endpush
@endsection 