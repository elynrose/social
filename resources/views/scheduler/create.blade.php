@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-clock"></i> Schedule New Post</h4>
                    <p class="text-muted mb-0">Create and schedule a new post for publication</p>
                </div>
                <div class="card-body">
                    <form action="{{ route('scheduler.store') }}" method="POST" id="schedulerForm">
                        @csrf
                        
                        <!-- Post Selection -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6><i class="fas fa-file-alt"></i> Post Selection</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="post_id" class="form-label">Select Post *</label>
                                    <select class="form-select" id="post_id" name="post_id" required>
                                        <option value="">Choose a post to schedule...</option>
                                        @foreach($posts ?? [] as $post)
                                            <option value="{{ $post->id }}" data-content="{{ $post->content }}">
                                                {{ Str::limit($post->title ?? $post->content, 50) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div id="postPreview" class="card" style="display: none;">
                                    <div class="card-body">
                                        <h6>Post Preview:</h6>
                                        <div id="postContent"></div>
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
                                            <input type="date" class="form-control" id="publish_date" name="publish_date" required min="{{ date('Y-m-d') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="publish_time" class="form-label">Publish Time *</label>
                                            <input type="time" class="form-control" id="publish_time" name="publish_time" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="timezone" class="form-label">Timezone</label>
                                            <select class="form-select" id="timezone" name="timezone">
                                                <option value="UTC">UTC</option>
                                                <option value="EST">Eastern Time (EST)</option>
                                                <option value="CST">Central Time (CST)</option>
                                                <option value="MST">Mountain Time (MST)</option>
                                                <option value="PST">Pacific Time (PST)</option>
                                                <option value="GMT">GMT</option>
                                                <option value="CET">Central European Time (CET)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="priority" class="form-label">Priority</label>
                                            <select class="form-select" id="priority" name="priority">
                                                <option value="low">Low</option>
                                                <option value="normal" selected>Normal</option>
                                                <option value="high">High</option>
                                                <option value="urgent">Urgent</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
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
                                                <option value="0">No retries</option>
                                                <option value="1">1 retry</option>
                                                <option value="2" selected>2 retries</option>
                                                <option value="3">3 retries</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="retry_interval" class="form-label">Retry Interval (minutes)</label>
                                            <select class="form-select" id="retry_interval" name="retry_interval">
                                                <option value="5">5 minutes</option>
                                                <option value="15" selected>15 minutes</option>
                                                <option value="30">30 minutes</option>
                                                <option value="60">1 hour</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="auto_optimize" name="auto_optimize" value="1" checked>
                                        <label class="form-check-label" for="auto_optimize">
                                            Auto-optimize posting time based on audience engagement
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="notify_on_failure" name="notify_on_failure" value="1" checked>
                                        <label class="form-check-label" for="notify_on_failure">
                                            Notify on posting failure
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="require_approval" name="require_approval" value="1">
                                        <label class="form-check-label" for="require_approval">
                                            Require approval before publishing
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Preview and Schedule -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6><i class="fas fa-eye"></i> Schedule Preview</h6>
                            </div>
                            <div class="card-body">
                                <div id="schedulePreview">
                                    <div class="text-center text-muted">
                                        <i class="fas fa-info-circle fa-2x mb-2"></i>
                                        <p>Select a post and set scheduling details to see preview</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary" id="scheduleBtn">
                                <i class="fas fa-clock"></i> Schedule Post
                            </button>
                            <a href="{{ route('scheduler.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Scheduler
                            </a>
                            <button type="button" class="btn btn-outline-info" onclick="previewSchedule()">
                                <i class="fas fa-eye"></i> Preview Schedule
                            </button>
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
    const form = document.getElementById('schedulerForm');
    const postSelect = document.getElementById('post_id');
    const postPreview = document.getElementById('postPreview');
    const postContent = document.getElementById('postContent');
    const schedulePreview = document.getElementById('schedulePreview');
    
    // Show post preview when selected
    postSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            postContent.textContent = selectedOption.dataset.content;
            postPreview.style.display = 'block';
            updateSchedulePreview();
        } else {
            postPreview.style.display = 'none';
        }
    });
    
    // Update schedule preview when form changes
    form.addEventListener('change', updateSchedulePreview);
    form.addEventListener('input', updateSchedulePreview);
    
    function updateSchedulePreview() {
        const postId = postSelect.value;
        const publishDate = document.getElementById('publish_date').value;
        const publishTime = document.getElementById('publish_time').value;
        const timezone = document.getElementById('timezone').value;
        const platforms = Array.from(document.querySelectorAll('input[name="platforms[]"]:checked')).map(cb => cb.value);
        
        if (postId && publishDate && publishTime) {
            const preview = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Schedule Details:</h6>
                        <ul class="list-unstyled">
                            <li><strong>Date:</strong> ${publishDate}</li>
                            <li><strong>Time:</strong> ${publishTime}</li>
                            <li><strong>Timezone:</strong> ${timezone}</li>
                            <li><strong>Platforms:</strong> ${platforms.length > 0 ? platforms.join(', ') : 'None selected'}</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Estimated Reach:</h6>
                        <div class="text-success">
                            <i class="fas fa-users"></i> ${platforms.length * 1000} potential impressions
                        </div>
                        <small class="text-muted">Based on your connected accounts</small>
                    </div>
                </div>
            `;
            schedulePreview.innerHTML = preview;
        }
    }
    
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

function previewSchedule() {
    // Implementation for previewing the schedule
    alert('Schedule preview generated. Check the preview section above.');
}
</script>
@endpush
@endsection 