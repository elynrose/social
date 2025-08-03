@extends('layouts.app')

@section('content')
<div class="container">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Welcome back, {{ auth()->user()->name }}!</h1>
                    <p class="text-muted mb-0">Here's what's happening with your social media presence today.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('posts.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Create Post
                    </a>
                    <a href="{{ route('calendar.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-calendar me-2"></i>View Calendar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics Row -->
    <div class="row g-4 mb-4">
        <!-- Content Metrics -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-file-alt text-primary fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title text-muted mb-1">Total Posts</h6>
                            <h3 class="mb-0">{{ number_format($totalPosts) }}</h3>
                            <small class="text-success">
                                <i class="fas fa-arrow-up me-1"></i>{{ $publishedPosts }} published
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-clock text-warning fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title text-muted mb-1">Scheduled</h6>
                            <h3 class="mb-0">{{ number_format($scheduledPosts) }}</h3>
                            <small class="text-warning">
                                <i class="fas fa-calendar me-1"></i>{{ $draftPosts }} drafts
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-heart text-success fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title text-muted mb-1">Total Engagement</h6>
                            <h3 class="mb-0">{{ number_format($totalEngagement) }}</h3>
                            <small class="text-success">
                                <i class="fas fa-arrow-up me-1"></i>{{ $engagementGrowth }}% this month
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-eye text-info fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title text-muted mb-1">Total Reach</h6>
                            <h3 class="mb-0">{{ number_format($totalReach) }}</h3>
                            <small class="text-info">
                                <i class="fas fa-arrow-up me-1"></i>{{ $reachGrowth }}% this month
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Analytics Row -->
    <div class="row g-4 mb-4">
        <!-- Performance Chart -->
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">Performance Overview (Last 7 Days)</h5>
                </div>
                <div class="card-body">
                    <canvas id="performanceChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- Platform Distribution -->
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">Platform Distribution</h5>
                </div>
                <div class="card-body">
                    @foreach($platformData as $platform)
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center">
                            <div class="platform-icon me-3">
                                @switch(strtolower($platform['platform']))
                                    @case('facebook')
                                        <i class="fab fa-facebook text-primary"></i>
                                        @break
                                    @case('twitter')
                                        <i class="fab fa-twitter text-info"></i>
                                        @break
                                    @case('linkedin')
                                        <i class="fab fa-linkedin text-primary"></i>
                                        @break
                                    @case('instagram')
                                        <i class="fab fa-instagram text-danger"></i>
                                        @break
                                    @default
                                        <i class="fas fa-globe text-secondary"></i>
                                @endswitch
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $platform['platform'] }}</h6>
                                <small class="text-muted">{{ $platform['posts'] }} posts</small>
                            </div>
                        </div>
                        <div class="text-end">
                            <h6 class="mb-0">{{ number_format($platform['engagement']) }}</h6>
                            <small class="text-muted">engagement</small>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Content and Activity Row -->
    <div class="row g-4 mb-4">
        <!-- Recent Posts -->
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Recent Posts</h5>
                    <a href="{{ route('posts.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    @if($recentPosts->count() > 0)
                        @foreach($recentPosts as $post)
                        <div class="d-flex align-items-start mb-3 pb-3 border-bottom">
                            <div class="flex-shrink-0 me-3">
                                <div class="bg-light rounded-circle p-2">
                                    <i class="fas fa-file-alt text-muted"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ Str::limit($post->content, 60) }}</h6>
                                <div class="d-flex align-items-center text-muted small">
                                    <span class="badge bg-{{ $post->status === 'published' ? 'success' : ($post->status === 'draft' ? 'warning' : 'secondary') }} me-2">
                                        {{ ucfirst($post->status) }}
                                    </span>
                                    <span><i class="far fa-clock me-1"></i>{{ $post->created_at->diffForHumans() }}</span>
                                    @if($post->campaign)
                                        <span class="ms-2"><i class="fas fa-bullhorn me-1"></i>{{ $post->campaign->name }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-file-alt fa-2x text-muted mb-3"></i>
                            <p class="text-muted">No posts yet. Create your first post!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Upcoming Posts -->
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Upcoming Posts</h5>
                    <a href="{{ route('calendar.index') }}" class="btn btn-sm btn-outline-primary">View Calendar</a>
                </div>
                <div class="card-body">
                    @if($upcomingPosts->count() > 0)
                        @foreach($upcomingPosts as $scheduledPost)
                        <div class="d-flex align-items-start mb-3 pb-3 border-bottom">
                            <div class="flex-shrink-0 me-3">
                                <div class="bg-warning bg-opacity-10 rounded-circle p-2">
                                    <i class="fas fa-clock text-warning"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ Str::limit($scheduledPost->post->content, 60) }}</h6>
                                <div class="d-flex align-items-center text-muted small">
                                    <span><i class="far fa-calendar me-1"></i>{{ $scheduledPost->publish_at->format('M j, Y g:i A') }}</span>
                                    @if($scheduledPost->post->campaign)
                                        <span class="ms-2"><i class="fas fa-bullhorn me-1"></i>{{ $scheduledPost->post->campaign->name }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-calendar fa-2x text-muted mb-3"></i>
                            <p class="text-muted">No upcoming posts scheduled.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions and Notifications Row -->
    <div class="row g-4">
        <!-- Quick Actions -->
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <a href="{{ route('posts.create') }}" class="card text-decoration-none h-100">
                                <div class="card-body text-center p-3">
                                    <i class="fas fa-plus-circle fa-2x text-primary mb-2"></i>
                                    <h6 class="card-title">Create Post</h6>
                                </div>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('calendar.index') }}" class="card text-decoration-none h-100">
                                <div class="card-body text-center p-3">
                                    <i class="fas fa-calendar-alt fa-2x text-info mb-2"></i>
                                    <h6 class="card-title">Schedule Post</h6>
                                </div>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('analytics.index') }}" class="card text-decoration-none h-100">
                                <div class="card-body text-center p-3">
                                    <i class="fas fa-chart-line fa-2x text-success mb-2"></i>
                                    <h6 class="card-title">View Analytics</h6>
                                </div>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('approval.index') }}" class="card text-decoration-none h-100">
                                <div class="card-body text-center p-3">
                                    <i class="fas fa-check-circle fa-2x text-warning mb-2"></i>
                                    <h6 class="card-title">Review Approvals</h6>
                                    @if($pendingApprovals > 0)
                                        <span class="badge bg-danger position-absolute top-0 end-0 mt-2 me-2">{{ $pendingApprovals }}</span>
                                    @endif
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notifications -->
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Recent Notifications</h5>
                    @if($unreadNotifications > 0)
                        <span class="badge bg-primary">{{ $unreadNotifications }} new</span>
                    @endif
                </div>
                <div class="card-body">
                    @if($recentNotifications->count() > 0)
                        @foreach($recentNotifications as $notification)
                        <div class="d-flex align-items-start mb-3 pb-3 border-bottom">
                            <div class="flex-shrink-0 me-3">
                                <div class="bg-light rounded-circle p-2">
                                    <i class="fas fa-bell text-muted"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $notification->title }}</h6>
                                <p class="text-muted small mb-1">{{ $notification->body }}</p>
                                <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-bell fa-2x text-muted mb-3"></i>
                            <p class="text-muted">No notifications yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Top Performing Posts -->
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">Top Performing Posts</h5>
                </div>
                <div class="card-body">
                    @if($topPosts->count() > 0)
                        @foreach($topPosts as $post)
                        <div class="d-flex align-items-start mb-3 pb-3 border-bottom">
                            <div class="flex-shrink-0 me-3">
                                <div class="bg-success bg-opacity-10 rounded-circle p-2">
                                    <i class="fas fa-star text-success"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ Str::limit($post->content, 50) }}</h6>
                                <div class="d-flex align-items-center text-muted small">
                                    <span><i class="fas fa-heart me-1"></i>{{ number_format($post->engagement) }} engagement</span>
                                    <span class="ms-2"><i class="fas fa-eye me-1"></i>{{ number_format($post->reach) }} reach</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-star fa-2x text-muted mb-3"></i>
                            <p class="text-muted">No published posts yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.platform-icon i {
    font-size: 1.2rem;
}
.card {
    transition: transform 0.2s ease-in-out;
}
.card:hover {
    transform: translateY(-2px);
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Performance Chart
    const ctx = document.getElementById('performanceChart').getContext('2d');
    const performanceData = @json($performanceData);
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: performanceData.map(item => item.date),
            datasets: [{
                label: 'Engagement',
                data: performanceData.map(item => item.engagement),
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                tension: 0.4
            }, {
                label: 'Reach',
                data: performanceData.map(item => item.reach),
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4
            }, {
                label: 'Clicks',
                data: performanceData.map(item => item.clicks),
                borderColor: '#ffc107',
                backgroundColor: 'rgba(255, 193, 7, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
@endpush
@endsection