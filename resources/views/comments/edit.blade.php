@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-edit"></i> Edit Comment</h4>
                    <p class="text-muted mb-0">Modify comment details and settings</p>
                </div>
                <div class="card-body">
                    <form action="{{ route('comments.update', $comment->id) }}" method="POST" id="commentEditForm">
                        @csrf
                        @method('PATCH')
                        
                        <div class="mb-3">
                            <label class="form-label">Post</label>
                            <input type="text" class="form-control" value="{{ $comment->post->title ?? $comment->post->content }}" readonly>
                            <div class="form-text">This comment belongs to the above post</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="content" class="form-label">Comment Content *</label>
                            <textarea class="form-control" id="content" name="content" rows="4" placeholder="Write your comment here..." required>{{ $comment->content }}</textarea>
                            <div class="form-text">Be respectful and constructive in your comment.</div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sentiment" class="form-label">Sentiment</label>
                                    <select class="form-select" id="sentiment" name="sentiment">
                                        <option value="positive" {{ $comment->sentiment === 'positive' ? 'selected' : '' }}>Positive</option>
                                        <option value="neutral" {{ $comment->sentiment === 'neutral' ? 'selected' : '' }}>Neutral</option>
                                        <option value="negative" {{ $comment->sentiment === 'negative' ? 'selected' : '' }}>Negative</option>
                                        <option value="mixed" {{ $comment->sentiment === 'mixed' ? 'selected' : '' }}>Mixed</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="pending" {{ $comment->status === 'pending' ? 'selected' : '' }}>Pending Review</option>
                                        <option value="approved" {{ $comment->status === 'approved' ? 'selected' : '' }}>Approved</option>
                                        <option value="rejected" {{ $comment->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                        <option value="spam" {{ $comment->status === 'spam' ? 'selected' : '' }}>Spam</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Comment Type</label>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" id="type_regular" name="type" value="regular" {{ $comment->type === 'regular' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="type_regular">Regular Comment</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" id="type_reply" name="type" value="reply" {{ $comment->type === 'reply' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="type_reply">Reply</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" id="type_moderated" name="type" value="moderated" {{ $comment->type === 'moderated' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="type_moderated">Moderated</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_pinned" name="is_pinned" value="1" {{ $comment->is_pinned ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_pinned">
                                    Pin this comment to the top
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" {{ $comment->is_featured ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_featured">
                                    Mark as featured comment
                                </label>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Comment
                            </button>
                            <a href="{{ route('comments.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Comments
                            </a>
                            <button type="button" class="btn btn-danger" onclick="deleteComment()">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </form>
                    
                    <!-- Comment History -->
                    <div class="mt-4">
                        <h6><i class="fas fa-history"></i> Comment History</h6>
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <small class="text-muted">Created:</small>
                                        <p class="mb-1">{{ $comment->created_at->format('M d, Y H:i') }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">Last Updated:</small>
                                        <p class="mb-1">{{ $comment->updated_at->format('M d, Y H:i') }}</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <small class="text-muted">Author:</small>
                                        <p class="mb-1">{{ $comment->user->name ?? 'Unknown' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">Platform:</small>
                                        <p class="mb-1">{{ $comment->platform ?? 'Web' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this comment? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('comments.destroy', $comment->id) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Comment</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('commentEditForm');
    
    form.addEventListener('submit', function(e) {
        const content = document.getElementById('content').value.trim();
        
        if (!content) {
            e.preventDefault();
            alert('Please enter comment content.');
            return;
        }
    });
    
    // Auto-detect sentiment based on content
    const contentTextarea = document.getElementById('content');
    const sentimentSelect = document.getElementById('sentiment');
    
    contentTextarea.addEventListener('input', function() {
        const content = this.value.toLowerCase();
        const positiveWords = ['great', 'awesome', 'amazing', 'love', 'good', 'excellent', 'wonderful', 'fantastic'];
        const negativeWords = ['bad', 'terrible', 'awful', 'hate', 'dislike', 'poor', 'worst', 'horrible'];
        
        let positiveCount = 0;
        let negativeCount = 0;
        
        positiveWords.forEach(word => {
            if (content.includes(word)) positiveCount++;
        });
        
        negativeWords.forEach(word => {
            if (content.includes(word)) negativeCount++;
        });
        
        if (positiveCount > negativeCount && positiveCount > 0) {
            sentimentSelect.value = 'positive';
        } else if (negativeCount > positiveCount && negativeCount > 0) {
            sentimentSelect.value = 'negative';
        } else if (positiveCount === negativeCount && positiveCount > 0) {
            sentimentSelect.value = 'mixed';
        } else {
            sentimentSelect.value = 'neutral';
        }
    });
});

function deleteComment() {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}
</script>
@endpush
@endsection 