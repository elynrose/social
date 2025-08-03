@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-comment"></i> Create New Comment</h4>
                    <p class="text-muted mb-0">Add a new comment to a post</p>
                </div>
                <div class="card-body">
                    <form action="{{ route('comments.store') }}" method="POST" id="commentForm">
                        @csrf
                        <div class="mb-3">
                            <label for="post_id" class="form-label">Select Post *</label>
                            <select class="form-select" id="post_id" name="post_id" required>
                                <option value="">Choose a post...</option>
                                @foreach($posts ?? [] as $post)
                                    <option value="{{ $post->id }}">{{ $post->title ?? $post->content }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="content" class="form-label">Comment Content *</label>
                            <textarea class="form-control" id="content" name="content" rows="4" placeholder="Write your comment here..." required></textarea>
                            <div class="form-text">Be respectful and constructive in your comment.</div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sentiment" class="form-label">Sentiment</label>
                                    <select class="form-select" id="sentiment" name="sentiment">
                                        <option value="positive">Positive</option>
                                        <option value="neutral">Neutral</option>
                                        <option value="negative">Negative</option>
                                        <option value="mixed">Mixed</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="pending">Pending Review</option>
                                        <option value="approved">Approved</option>
                                        <option value="rejected">Rejected</option>
                                        <option value="spam">Spam</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Comment Type</label>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" id="type_regular" name="type" value="regular" checked>
                                        <label class="form-check-label" for="type_regular">Regular Comment</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" id="type_reply" name="type" value="reply">
                                        <label class="form-check-label" for="type_reply">Reply</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" id="type_moderated" name="type" value="moderated">
                                        <label class="form-check-label" for="type_moderated">Moderated</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_pinned" name="is_pinned" value="1">
                                <label class="form-check-label" for="is_pinned">
                                    Pin this comment to the top
                                </label>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Comment
                            </button>
                            <a href="{{ route('comments.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Comments
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('commentForm');
    
    form.addEventListener('submit', function(e) {
        const content = document.getElementById('content').value.trim();
        const postId = document.getElementById('post_id').value;
        
        if (!content) {
            e.preventDefault();
            alert('Please enter comment content.');
            return;
        }
        
        if (!postId) {
            e.preventDefault();
            alert('Please select a post.');
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
</script>
@endpush
@endsection 