@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-at"></i> Mention Details</h4>
                    <p class="text-muted mb-0">View and manage mention information</p>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <!-- Mention Content -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6><i class="fas fa-quote-left"></i> Mention Content</h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex align-items-start mb-3">
                                        <div class="avatar me-3">
                                            <i class="fab fa-{{ strtolower($mention->platform) }} fa-2x text-primary"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">{{ $mention->author_name ?? 'Unknown Author' }}</h6>
                                            <small class="text-muted">
                                                @{{ $mention->author_username }} • {{ $mention->created_at->format('M d, Y H:i') }}
                                            </small>
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#" onclick="replyToMention({{ $mention->id }})">
                                                    <i class="fas fa-reply"></i> Reply
                                                </a></li>
                                                <li><a class="dropdown-item" href="#" onclick="likeMention({{ $mention->id }})">
                                                    <i class="fas fa-heart"></i> Like
                                                </a></li>
                                                <li><a class="dropdown-item" href="#" onclick="shareMention({{ $mention->id }})">
                                                    <i class="fas fa-share"></i> Share
                                                </a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-success" href="#" onclick="approveMention({{ $mention->id }})">
                                                    <i class="fas fa-check"></i> Approve
                                                </a></li>
                                                <li><a class="dropdown-item text-danger" href="#" onclick="rejectMention({{ $mention->id }})">
                                                    <i class="fas fa-times"></i> Reject
                                                </a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="mention-content">
                                        {!! nl2br(e($mention->content)) !!}
                                    </div>
                                    @if($mention->media_url)
                                        <div class="mt-3">
                                            <img src="{{ $mention->media_url }}" alt="Mention media" class="img-fluid rounded" style="max-height: 300px;">
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Sentiment Analysis -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6><i class="fas fa-heart"></i> Sentiment Analysis</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="sentiment-icon me-3">
                                                    @if($mention->sentiment === 'positive')
                                                        <i class="fas fa-smile text-success fa-2x"></i>
                                                    @elseif($mention->sentiment === 'negative')
                                                        <i class="fas fa-frown text-danger fa-2x"></i>
                                                    @else
                                                        <i class="fas fa-meh text-warning fa-2x"></i>
                                                    @endif
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ ucfirst($mention->sentiment) }}</h6>
                                                    <small class="text-muted">Confidence: {{ $mention->sentiment_confidence ?? 85 }}%</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Engagement Score:</span>
                                                <strong>{{ $mention->engagement_score ?? 'N/A' }}</strong>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Reach:</span>
                                                <strong>{{ $mention->reach ?? 'N/A' }}</strong>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span>Influence Score:</span>
                                                <strong>{{ $mention->influence_score ?? 'N/A' }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <!-- Mention Details -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6><i class="fas fa-info-circle"></i> Details</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <small class="text-muted">Platform</small>
                                        <div class="d-flex align-items-center">
                                            <i class="fab fa-{{ strtolower($mention->platform) }} me-2"></i>
                                            <span>{{ ucfirst($mention->platform) }}</span>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <small class="text-muted">Status</small>
                                        <div>
                                            <span class="badge bg-{{ $mention->status === 'approved' ? 'success' : ($mention->status === 'pending' ? 'warning' : 'danger') }}">
                                                {{ ucfirst($mention->status) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <small class="text-muted">Type</small>
                                        <div>
                                            <span class="badge bg-info">{{ ucfirst($mention->type) }}</span>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <small class="text-muted">Priority</small>
                                        <div>
                                            <span class="badge bg-{{ $mention->priority === 'high' ? 'danger' : ($mention->priority === 'medium' ? 'warning' : 'success') }}">
                                                {{ ucfirst($mention->priority) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <small class="text-muted">Created</small>
                                        <div>{{ $mention->created_at->format('M d, Y H:i') }}</div>
                                    </div>
                                    <div class="mb-3">
                                        <small class="text-muted">Last Updated</small>
                                        <div>{{ $mention->updated_at->format('M d, Y H:i') }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Author Information -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6><i class="fas fa-user"></i> Author Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar me-3">
                                            @if($mention->author_avatar)
                                                <img src="{{ $mention->author_avatar }}" alt="Author avatar" class="rounded-circle" width="50" height="50">
                                            @else
                                                <i class="fas fa-user-circle fa-2x text-muted"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $mention->author_name ?? 'Unknown' }}</h6>
                                            <small class="text-muted">@{{ $mention->author_username }}</small>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-muted">Followers</small>
                                        <div>{{ number_format($mention->author_followers ?? 0) }}</div>
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-muted">Following</small>
                                        <div>{{ number_format($mention->author_following ?? 0) }}</div>
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-muted">Posts</small>
                                        <div>{{ number_format($mention->author_posts ?? 0) }}</div>
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-muted">Verified</small>
                                        <div>
                                            @if($mention->author_verified)
                                                <i class="fas fa-check-circle text-success"></i> Yes
                                            @else
                                                <i class="fas fa-times-circle text-muted"></i> No
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="card">
                                <div class="card-header">
                                    <h6><i class="fas fa-cogs"></i> Actions</h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <button class="btn btn-primary" onclick="replyToMention({{ $mention->id }})">
                                            <i class="fas fa-reply"></i> Reply
                                        </button>
                                        <button class="btn btn-outline-success" onclick="approveMention({{ $mention->id }})">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                        <button class="btn btn-outline-danger" onclick="rejectMention({{ $mention->id }})">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                        <button class="btn btn-outline-warning" onclick="flagMention({{ $mention->id }})">
                                            <i class="fas fa-flag"></i> Flag
                                        </button>
                                        <a href="{{ route('mentions.index') }}" class="btn btn-outline-secondary">
                                            <i class="fas fa-arrow-left"></i> Back to Mentions
                                        </a>
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

@push('scripts')
<script>
function replyToMention(mentionId) {
    // Implementation for replying to mention
    alert('Reply functionality will be implemented here');
}

function likeMention(mentionId) {
    // Implementation for liking mention
    alert('Like functionality will be implemented here');
}

function shareMention(mentionId) {
    // Implementation for sharing mention
    alert('Share functionality will be implemented here');
}

function approveMention(mentionId) {
    if (confirm('Are you sure you want to approve this mention?')) {
        // Implementation for approving mention
        alert('Mention approved!');
    }
}

function rejectMention(mentionId) {
    if (confirm('Are you sure you want to reject this mention?')) {
        // Implementation for rejecting mention
        alert('Mention rejected!');
    }
}

function flagMention(mentionId) {
    if (confirm('Are you sure you want to flag this mention?')) {
        // Implementation for flagging mention
        alert('Mention flagged!');
    }
}
</script>
@endpush
@endsection 