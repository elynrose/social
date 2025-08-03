@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-at"></i> Social Media Mentions
                    </h5>
                    <div class="btn-group">
                        <a href="{{ route('mentions.analytics') }}" class="btn btn-info btn-sm">
                            <i class="fas fa-chart-bar"></i> Analytics
                        </a>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="refreshMentions()">
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
                                    <h6 class="mb-0">Filter Mentions</h6>
                                </div>
                                <div class="card-body">
                                    <form method="GET" action="{{ route('mentions.index') }}" class="row">
                                        <div class="col-md-3">
                                            <label for="platform" class="form-label">Platform</label>
                                            <select name="platform" id="platform" class="form-select">
                                                <option value="">All Platforms</option>
                                                @foreach($platforms as $platform)
                                                    <option value="{{ $platform }}" {{ request('platform') == $platform ? 'selected' : '' }}>
                                                        {{ ucfirst($platform) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="sentiment" class="form-label">Sentiment</label>
                                            <select name="sentiment" id="sentiment" class="form-select">
                                                <option value="">All Sentiments</option>
                                                @foreach($sentiments as $sentiment)
                                                    <option value="{{ $sentiment }}" {{ request('sentiment') == $sentiment ? 'selected' : '' }}>
                                                        {{ ucfirst($sentiment) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="date" class="form-label">Date</label>
                                            <input type="date" name="date" id="date" class="form-control" value="{{ request('date') }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">&nbsp;</label>
                                            <div>
                                                <button type="submit" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-filter"></i> Filter
                                                </button>
                                                <a href="{{ route('mentions.index') }}" class="btn btn-outline-secondary btn-sm">
                                                    <i class="fas fa-times"></i> Clear
                                                </a>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mentions List -->
                    <div class="row">
                        <div class="col-md-12">
                            @if($mentions->count() > 0)
                                <div class="mentions-list">
                                    @foreach($mentions as $mention)
                                        <div class="card mb-3 mention-item">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <div class="d-flex align-items-center">
                                                        <div class="platform-icon me-3">
                                                            @switch($mention->platform)
                                                                @case('facebook')
                                                                    <i class="fab fa-facebook fa-2x text-primary"></i>
                                                                    @break
                                                                @case('twitter')
                                                                    <i class="fab fa-twitter fa-2x text-info"></i>
                                                                    @break
                                                                @case('linkedin')
                                                                    <i class="fab fa-linkedin fa-2x text-primary"></i>
                                                                    @break
                                                                @case('instagram')
                                                                    <i class="fab fa-instagram fa-2x text-danger"></i>
                                                                    @break
                                                                @default
                                                                    <i class="fas fa-globe fa-2x text-secondary"></i>
                                                            @endswitch
                                                        </div>
                                                        <div>
                                                            <strong>{{ $mention->author_name ?? 'Unknown Author' }}</strong>
                                                            <br><small class="text-muted">{{ $mention->posted_at->diffForHumans() }}</small>
                                                        </div>
                                                    </div>
                                                    <div class="btn-group">
                                                        <a href="{{ route('mentions.show', $mention) }}" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        @can('update', $mention)
                                                            <button type="button" class="btn btn-sm btn-outline-success" 
                                                                    onclick="updateMentionStatus({{ $mention->id }})">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                        @endcan
                                                        @can('delete', $mention)
                                                            <form action="{{ route('mentions.destroy', $mention) }}" 
                                                                  method="POST" 
                                                                  class="d-inline"
                                                                  onsubmit="return confirm('Are you sure you want to delete this mention?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        @endcan
                                                    </div>
                                                </div>
                                                
                                                <div class="mention-content mb-3">
                                                    <p class="mb-0">{{ $mention->content }}</p>
                                                </div>
                                                
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <small class="text-muted">
                                                            <strong>Platform:</strong> {{ ucfirst($mention->platform) }}
                                                        </small>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <small class="text-muted">
                                                            <strong>Sentiment:</strong> 
                                                            @switch($mention->sentiment)
                                                                @case('positive')
                                                                    <span class="badge bg-success">Positive</span>
                                                                    @break
                                                                @case('negative')
                                                                    <span class="badge bg-danger">Negative</span>
                                                                    @break
                                                                @case('neutral')
                                                                    <span class="badge bg-secondary">Neutral</span>
                                                                    @break
                                                                @default
                                                                    <span class="badge bg-secondary">{{ ucfirst($mention->sentiment) }}</span>
                                                            @endswitch
                                                        </small>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <small class="text-muted">
                                                            <strong>Status:</strong> 
                                                            @switch($mention->status)
                                                                @case('new')
                                                                    <span class="badge bg-primary">New</span>
                                                                    @break
                                                                @case('reviewed')
                                                                    <span class="badge bg-info">Reviewed</span>
                                                                    @break
                                                                @case('responded')
                                                                    <span class="badge bg-success">Responded</span>
                                                                    @break
                                                                @case('ignored')
                                                                    <span class="badge bg-secondary">Ignored</span>
                                                                    @break
                                                                @default
                                                                    <span class="badge bg-secondary">{{ ucfirst($mention->status) }}</span>
                                                            @endswitch
                                                        </small>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <small class="text-muted">
                                                            <strong>Engagement:</strong> 
                                                            @if($mention->likes_count > 0)
                                                                <i class="fas fa-thumbs-up"></i> {{ $mention->likes_count }}
                                                            @endif
                                                            @if($mention->comments_count > 0)
                                                                <i class="fas fa-comments"></i> {{ $mention->comments_count }}
                                                            @endif
                                                            @if($mention->shares_count > 0)
                                                                <i class="fas fa-share"></i> {{ $mention->shares_count }}
                                                            @endif
                                                        </small>
                                                    </div>
                                                </div>
                                                
                                                @if($mention->url)
                                                    <div class="mt-2">
                                                        <a href="{{ $mention->url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-external-link-alt"></i> View Original
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                
                                <!-- Pagination -->
                                <div class="d-flex justify-content-center mt-4">
                                    {{ $mentions->links() }}
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-at fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No Mentions Found</h5>
                                    <p class="text-muted">No social media mentions match your current filters.</p>
                                    <a href="{{ route('mentions.index') }}" class="btn btn-primary">
                                        <i class="fas fa-eye"></i> View All Mentions
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

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Mention Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="updateStatusForm" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select" required>
                            <option value="new">New</option>
                            <option value="reviewed">Reviewed</option>
                            <option value="responded">Responded</option>
                            <option value="ignored">Ignored</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes (Optional)</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="updateStatusForm" class="btn btn-primary">Update Status</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function updateMentionStatus(mentionId) {
    const modal = new bootstrap.Modal(document.getElementById('updateStatusModal'));
    const form = document.getElementById('updateStatusForm');
    form.action = `/mentions/${mentionId}`;
    modal.show();
}

function refreshMentions() {
    location.reload();
}
</script>
@endpush
@endsection 