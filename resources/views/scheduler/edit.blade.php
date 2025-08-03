@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-edit"></i> Edit Scheduled Post</h4>
                    <p class="text-muted mb-0">Modify scheduling details for existing post</p>
                </div>
                <div class="card-body">
                    <form action="{{ route('scheduler.update', $scheduledPost->id) }}" method="POST" id="schedulerEditForm">
                        @csrf
                        @method('PATCH')
                        
                        <!-- Current Status -->
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> Current Status</h6>
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Status:</strong> 
                                    <span class="badge bg-{{ $scheduledPost->status === 'scheduled' ? 'warning' : ($scheduledPost->status === 'published' ? 'success' : 'danger') }}">
                                        {{ ucfirst($scheduledPost->status) }}
                                    </span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Created:</strong> {{ $scheduledPost->created_at->format('M d, Y H:i') }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Last Updated:</strong> {{ $scheduledPost->updated_at->format('M d, Y H:i') }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Attempts:</strong> {{ $scheduledPost->attempts ?? 0 }}
                                </div>
                            </div>
                        </div>

                        <!-- Post Information -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6><i class="fas fa-file-alt"></i> Post Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h6>{{ $scheduledPost->post->title ?? 'Untitled Post' }}</h6>
                                        <p class="text-muted">{{ Str::limit($scheduledPost->post->content, 200) }}</p>
                                        <small class="text-muted">
                                            <i class="fas fa-user"></i> {{ $scheduledPost->post->user->name ?? 'Unknown' }} | 
                                            <i class="fas fa-calendar"></i> {{ $scheduledPost->post->created_at->format('M d, Y') }}
                                        </small>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-end">
                                            <a href="{{ route('posts.edit', $scheduledPost->post->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i> Edit Post
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Scheduling Details -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6><i class="fas fa-calendar"></i> Scheduling Details</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="publish_date" class="form-label">Publish Date *</label>
                                            <input type="date" class="form-control" id="publish_date" name="publish_date" 
                                                   value="{{ $scheduledPost->publish_at->format('Y-m-d') }}" required 
                                                   min="{{ date('Y-m-d') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="publish_time" class="form-label">Publish Time *</label>
                                            <input type="time" class="form-control" id="publish_time" name="publish_time" 
                                                   value="{{ $scheduledPost->publish_at->format('H:i') }}" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="timezone" class="form-label">Timezone</label>
                                            <select class="form-select" id="timezone" name="timezone">
                                                <option value="UTC" {{ $scheduledPost->timezone === 'UTC' ? 'selected' : '' }}>UTC</option>
                                                <option value="EST" {{ $scheduledPost->timezone === 'EST' ? 'selected' : '' }}>Eastern Time (EST)</option>
                                                <option value="CST" {{ $scheduledPost->timezone === 'CST' ? 'selected' : '' }}>Central Time (CST)</option>
                                                <option value="MST" {{ $scheduledPost->timezone === 'MST' ? 'selected' : '' }}>Mountain Time (MST)</option>
                                                <option value="PST" {{ $scheduledPost->timezone === 'PST' ? 'selected' : '' }}>Pacific Time (PST)</option>
                                                <option value="GMT" {{ $scheduledPost->timezone === 'GMT' ? 'selected' : '' }}>GMT</option>
                                                <option value="CET" {{ $scheduledPost->timezone === 'CET' ? 'selected' : '' }}>Central European Time (CET)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="priority" class="form-label">Priority</label>
                                            <select class="form-select" id="priority" name="priority">
                                                <option value="low" {{ $scheduledPost->priority === 'low' ? 'selected' : '' }}>Low</option>
                                                <option value="normal" {{ $scheduledPost->priority === 'normal' ? 'selected' : '' }}>Normal</option>
                                                <option value="high" {{ $scheduledPost->priority === 'high' ? 'selected' : '' }}>High</option>
                                                <option value="urgent" {{ $scheduledPost->priority === 'urgent' ? 'selected' : '' }}>Urgent</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Platform Selection -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6><i class="fas fa-share-alt"></i> Platform Selection</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="platform_instagram" name="platforms[]" value="instagram" 
                                                   {{ in_array('instagram', $scheduledPost->platforms ?? []) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="platform_instagram">
                                                <i class="fab fa-instagram text-danger"></i> Instagram
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="platform_facebook" name="platforms[]" value="facebook" 
                                                   {{ in_array('facebook', $scheduledPost->platforms ?? []) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="platform_facebook">
                                                <i class="fab fa-facebook text-primary"></i> Facebook
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="platform_twitter" name="platforms[]" value="twitter" 
                                                   {{ in_array('twitter', $scheduledPost->platforms ?? []) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="platform_twitter">
                                                <i class="fab fa-twitter text-info"></i> Twitter
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="platform_linkedin" name="platforms[]" value="linkedin" 
                                                   {{ in_array('linkedin', $scheduledPost->platforms ?? []) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="platform_linkedin">
                                                <i class="fab fa-linkedin text-primary"></i> LinkedIn
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-3">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllPlatforms()">
                                        <i class="fas fa-check-square"></i> Select All
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearAllPlatforms()">
                                        <i class="fas fa-square"></i> Clear All
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Publishing Status -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6><i class="fas fa-chart-bar"></i> Publishing Status</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @foreach($scheduledPost->platforms ?? [] as $platform)
                                        <div class="col-md-3 mb-3">
                                            <div class="d-flex align-items-center">
                                                <i class="fab fa-{{ strtolower($platform) }} me-2"></i>
                                                <div class="flex-grow-1">
                                                    <div class="fw-bold">{{ ucfirst($platform) }}</div>
                                                    <small class="text-muted">
                                                        @if($scheduledPost->status === 'published')
                                                            <span class="text-success">Published</span>
                                                        @elseif($scheduledPost->status === 'failed')
                                                            <span class="text-danger">Failed</span>
                                                        @else
                                                            <span class="text-warning">Pending</span>
                                                        @endif
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                
                                @if($scheduledPost->status === 'failed')
                                    <div class="alert alert-danger">
                                        <h6><i class="fas fa-exclamation-triangle"></i> Publishing Failed</h6>
                                        <p class="mb-0">{{ $scheduledPost->error_message ?? 'Unknown error occurred during publishing.' }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Advanced Options -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6><i class="fas fa-cogs"></i> Advanced Options</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="retry_count" class="form-label">Retry Attempts</label>
                                            <select class="form-select" id="retry_count" name="retry_count">
                                                <option value="0" {{ ($scheduledPost->retry_count ?? 2) == 0 ? 'selected' : '' }}>No retries</option>
                                                <option value="1" {{ ($scheduledPost->retry_count ?? 2) == 1 ? 'selected' : '' }}>1 retry</option>
                                                <option value="2" {{ ($scheduledPost->retry_count ?? 2) == 2 ? 'selected' : '' }}>2 retries</option>
                                                <option value="3" {{ ($scheduledPost->retry_count ?? 2) == 3 ? 'selected' : '' }}>3 retries</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="retry_interval" class="form-label">Retry Interval (minutes)</label>
                                            <select class="form-select" id="retry_interval" name="retry_interval">
                                                <option value="5" {{ ($scheduledPost->retry_interval ?? 15) == 5 ? 'selected' : '' }}>5 minutes</option>
                                                <option value="15" {{ ($scheduledPost->retry_interval ?? 15) == 15 ? 'selected' : '' }}>15 minutes</option>
                                                <option value="30" {{ ($scheduledPost->retry_interval ?? 15) == 30 ? 'selected' : '' }}>30 minutes</option>
                                                <option value="60" {{ ($scheduledPost->retry_interval ?? 15) == 60 ? 'selected' : '' }}>1 hour</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="auto_optimize" name="auto_optimize" value="1" 
                                               {{ $scheduledPost->auto_optimize ? 'checked' : '' }}>
                                        <label class="form-check-label" for="auto_optimize">
                                            Auto-optimize posting time based on audience engagement
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="notify_on_failure" name="notify_on_failure" value="1" 
                                               {{ $scheduledPost->notify_on_failure ? 'checked' : '' }}>
                                        <label class="form-check-label" for="notify_on_failure">
                                            Send notification on posting failure
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="require_approval" name="require_approval" value="1" 
                                               {{ $scheduledPost->require_approval ? 'checked' : '' }}>
                                        <label class="form-check-label" for="require_approval">
                                            Require approval before publishing
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Schedule
                            </button>
                            <a href="{{ route('scheduler.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Scheduler
                            </a>
                            <button type="button" class="btn btn-success" onclick="publishNow()">
                                <i class="fas fa-play"></i> Publish Now
                            </button>
                            <button type="button" class="btn btn-danger" onclick="deleteSchedule()">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </form>
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
                <p>Are you sure you want to delete this scheduled post? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('scheduler.destroy', $scheduledPost->id) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Schedule</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('schedulerEditForm');
    
    // Form validation
    form.addEventListener('submit', function(e) {
        const platforms = document.querySelectorAll('input[name="platforms[]"]:checked');
        if (platforms.length === 0) {
            e.preventDefault();
            alert('Please select at least one platform to publish to.');
            return;
        }
    });
});

function selectAllPlatforms() {
    document.querySelectorAll('input[name="platforms[]"]').forEach(checkbox => {
        checkbox.checked = true;
    });
}

function clearAllPlatforms() {
    document.querySelectorAll('input[name="platforms[]"]').forEach(checkbox => {
        checkbox.checked = false;
    });
}

function publishNow() {
    if (confirm('Are you sure you want to publish this post immediately?')) {
        // Implementation for immediate publishing
        alert('Post published immediately!');
    }
}

function deleteSchedule() {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}
</script>
@endpush
@endsection 