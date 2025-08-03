@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line"></i> Analytics Overview
                    </h5>
                    <div class="btn-group">
                        <a href="{{ route('analytics.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Analytics
                        </a>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="refreshData()">
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

                    <!-- Key Metrics -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-heart fa-2x mb-2"></i>
                                    <h4>{{ number_format($stats['total_likes'] ?? 0) }}</h4>
                                    <p class="mb-0">Total Likes</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-comments fa-2x mb-2"></i>
                                    <h4>{{ number_format($stats['total_comments'] ?? 0) }}</h4>
                                    <p class="mb-0">Total Comments</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-share fa-2x mb-2"></i>
                                    <h4>{{ number_format($stats['total_shares'] ?? 0) }}</h4>
                                    <p class="mb-0">Total Shares</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-eye fa-2x mb-2"></i>
                                    <h4>{{ number_format($stats['total_views'] ?? 0) }}</h4>
                                    <p class="mb-0">Total Views</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Engagement Chart -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Engagement Over Time</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="engagementChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Platform Breakdown</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="platformChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Metrics -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Top Performing Posts</h6>
                                </div>
                                <div class="card-body">
                                    @if(isset($stats['top_posts']) && count($stats['top_posts']) > 0)
                                        <div class="list-group list-group-flush">
                                            @foreach($stats['top_posts'] as $post)
                                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <strong>{{ Str::limit($post['content'] ?? 'Post', 50) }}</strong>
                                                        <br><small class="text-muted">{{ $post['platform'] ?? 'Unknown' }}</small>
                                                    </div>
                                                    <div class="text-end">
                                                        <span class="badge bg-primary">{{ $post['likes'] ?? 0 }} likes</span>
                                                        <br><small class="text-muted">{{ $post['comments'] ?? 0 }} comments</small>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-muted text-center">No posts data available</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Recent Activity</h6>
                                </div>
                                <div class="card-body">
                                    @if(isset($stats['recent_activity']) && count($stats['recent_activity']) > 0)
                                        <div class="list-group list-group-flush">
                                            @foreach($stats['recent_activity'] as $activity)
                                                <div class="list-group-item">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-circle text-primary me-2"></i>
                                                        <div>
                                                            <strong>{{ $activity['type'] ?? 'Activity' }}</strong>
                                                            <br><small class="text-muted">{{ $activity['description'] ?? 'No description' }}</small>
                                                        </div>
                                                        <small class="text-muted ms-auto">{{ $activity['time'] ?? 'Unknown' }}</small>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-muted text-center">No recent activity</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Quick Actions</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <a href="{{ route('analytics.posts') }}" class="btn btn-outline-primary w-100 mb-2">
                                                <i class="fas fa-file-alt"></i> Top Posts
                                            </a>
                                        </div>
                                        <div class="col-md-3">
                                            <a href="{{ route('analytics.engagement') }}" class="btn btn-outline-success w-100 mb-2">
                                                <i class="fas fa-chart-bar"></i> Engagement Analysis
                                            </a>
                                        </div>
                                        <div class="col-md-3">
                                            <a href="{{ route('analytics.platform', 'facebook') }}" class="btn btn-outline-info w-100 mb-2">
                                                <i class="fab fa-facebook"></i> Facebook Analytics
                                            </a>
                                        </div>
                                        <div class="col-md-3">
                                            <a href="{{ route('analytics.platform', 'twitter') }}" class="btn btn-outline-info w-100 mb-2">
                                                <i class="fab fa-twitter"></i> Twitter Analytics
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
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Engagement Chart
    const engagementCtx = document.getElementById('engagementChart').getContext('2d');
    const engagementChart = new Chart(engagementCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Likes',
                data: [12, 19, 3, 5, 2, 3],
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }, {
                label: 'Comments',
                data: [8, 15, 7, 12, 9, 11],
                borderColor: 'rgb(255, 99, 132)',
                tension: 0.1
            }, {
                label: 'Shares',
                data: [5, 12, 4, 8, 6, 9],
                borderColor: 'rgb(255, 205, 86)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Engagement Trends'
                }
            }
        }
    });

    // Platform Chart
    const platformCtx = document.getElementById('platformChart').getContext('2d');
    const platformChart = new Chart(platformCtx, {
        type: 'doughnut',
        data: {
            labels: ['Facebook', 'Twitter', 'LinkedIn', 'Instagram'],
            datasets: [{
                data: [300, 150, 100, 200],
                backgroundColor: [
                    'rgb(255, 99, 132)',
                    'rgb(54, 162, 235)',
                    'rgb(255, 205, 86)',
                    'rgb(75, 192, 192)'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                title: {
                    display: true,
                    text: 'Platform Distribution'
                }
            }
        }
    });
});

function refreshData() {
    location.reload();
}
</script>
@endpush
@endsection 