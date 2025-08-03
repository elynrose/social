@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-clock"></i> Post Scheduler
                    </h5>
                    <div class="btn-group">
                        <a href="{{ route('scheduler.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Schedule Post
                        </a>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="refreshScheduler()">
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

                    <!-- Quick Stats -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-clock fa-2x mb-2"></i>
                                    <h4 id="scheduled-count">-</h4>
                                    <p class="mb-0">Scheduled</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-check fa-2x mb-2"></i>
                                    <h4 id="published-count">-</h4>
                                    <p class="mb-0">Published</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                                    <h4 id="failed-count">-</h4>
                                    <p class="mb-0">Failed</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-pause fa-2x mb-2"></i>
                                    <h4 id="pending-count">-</h4>
                                    <p class="mb-0">Pending</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Scheduled Posts List -->
                    <div class="row">
                        <div class="col-md-12">
                            <h6>Scheduled Posts</h6>
                            @if($scheduledPosts->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Post</th>
                                                <th>Platforms</th>
                                                <th>Scheduled For</th>
                                                <th>Time Zone</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($scheduledPosts as $scheduledPost)
                                                <tr>
                                                    <td>
                                                        <div>
                                                            <strong>{{ Str::limit($scheduledPost->post->content, 50) }}</strong>
                                                            @if($scheduledPost->post->campaign)
                                                                <br><small class="text-muted">Campaign: {{ $scheduledPost->post->campaign->name }}</small>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @if($scheduledPost->post->socialAccount)
                                                            @switch($scheduledPost->post->socialAccount->platform)
                                                                @case('facebook')
                                                                    <i class="fab fa-facebook text-primary"></i> Facebook
                                                                    @break
                                                                @case('twitter')
                                                                    <i class="fab fa-twitter text-info"></i> Twitter
                                                                    @break
                                                                @case('linkedin')
                                                                    <i class="fab fa-linkedin text-primary"></i> LinkedIn
                                                                    @break
                                                                @case('instagram')
                                                                    <i class="fab fa-instagram text-danger"></i> Instagram
                                                                    @break
                                                                @case('youtube')
                                                                    <i class="fab fa-youtube text-danger"></i> YouTube
                                                                    @break
                                                                @default
                                                                    <i class="fas fa-globe text-secondary"></i> {{ ucfirst($scheduledPost->post->socialAccount->platform ?? 'Unknown') }}
                                                            @endswitch
                                                        @else
                                                            <span class="text-muted">No platform</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <strong>{{ $scheduledPost->publish_at->format('M j, Y') }}</strong>
                                                            <br><small class="text-muted">{{ $scheduledPost->publish_at->format('g:i A') }}</small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">{{ $scheduledPost->time_zone }}</small>
                                                    </td>
                                                    <td>
                                                        @switch($scheduledPost->status)
                                                            @case('scheduled')
                                                                <span class="badge bg-primary">Scheduled</span>
                                                                @break
                                                            @case('published')
                                                                <span class="badge bg-success">Published</span>
                                                                @break
                                                            @case('failed')
                                                                <span class="badge bg-danger">Failed</span>
                                                                @break
                                                            @case('cancelled')
                                                                <span class="badge bg-secondary">Cancelled</span>
                                                                @break
                                                            @default
                                                                <span class="badge bg-secondary">{{ ucfirst($scheduledPost->status) }}</span>
                                                        @endswitch
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <a href="{{ route('scheduler.edit', $scheduledPost) }}" 
                                                               class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <form action="{{ route('scheduler.destroy', $scheduledPost) }}" 
                                                                  method="POST" 
                                                                  class="d-inline"
                                                                  onsubmit="return confirm('Are you sure you want to cancel this scheduled post?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Pagination -->
                                <div class="d-flex justify-content-center mt-4">
                                    {{ $scheduledPosts->links() }}
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No Scheduled Posts</h5>
                                    <p class="text-muted">Schedule your first post to see it here.</p>
                                    <a href="{{ route('scheduler.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Schedule Your First Post
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
document.addEventListener('DOMContentLoaded', function() {
    loadSchedulerStats();
});

function loadSchedulerStats() {
    // This would typically load from an API endpoint
    // For now, we'll calculate from the visible data
    const rows = document.querySelectorAll('tbody tr');
    let scheduled = 0, published = 0, failed = 0, pending = 0;
    
    rows.forEach(row => {
        const statusCell = row.querySelector('td:nth-child(5)');
        if (statusCell) {
            const status = statusCell.textContent.trim().toLowerCase();
            if (status.includes('scheduled')) scheduled++;
            else if (status.includes('published')) published++;
            else if (status.includes('failed')) failed++;
            else pending++;
        }
    });
    
    document.getElementById('scheduled-count').textContent = scheduled;
    document.getElementById('published-count').textContent = published;
    document.getElementById('failed-count').textContent = failed;
    document.getElementById('pending-count').textContent = pending;
}

function refreshScheduler() {
    location.reload();
}
</script>
@endpush
@endsection 