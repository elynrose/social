@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-file-alt"></i> Top Posts Analytics
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

                    <!-- Filter Options -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Filter Posts</h6>
                                </div>
                                <div class="card-body">
                                    <form method="GET" action="{{ route('analytics.posts') }}" class="row">
                                        <div class="col-md-3">
                                            <label for="platform" class="form-label">Platform</label>
                                            <select name="platform" id="platform" class="form-select">
                                                <option value="">All Platforms</option>
                                                <option value="facebook" {{ request('platform') == 'facebook' ? 'selected' : '' }}>Facebook</option>
                                                <option value="twitter" {{ request('platform') == 'twitter' ? 'selected' : '' }}>Twitter</option>
                                                <option value="linkedin" {{ request('platform') == 'linkedin' ? 'selected' : '' }}>LinkedIn</option>
                                                <option value="instagram" {{ request('platform') == 'instagram' ? 'selected' : '' }}>Instagram</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="period" class="form-label">Time Period</label>
                                            <select name="period" id="period" class="form-select">
                                                <option value="7" {{ request('period') == '7' ? 'selected' : '' }}>Last 7 Days</option>
                                                <option value="30" {{ request('period') == '30' ? 'selected' : '' }}>Last 30 Days</option>
                                                <option value="90" {{ request('period') == '90' ? 'selected' : '' }}>Last 90 Days</option>
                                                <option value="365" {{ request('period') == '365' ? 'selected' : '' }}>Last Year</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="sort" class="form-label">Sort By</label>
                                            <select name="sort" id="sort" class="form-select">
                                                <option value="likes" {{ request('sort') == 'likes' ? 'selected' : '' }}>Likes</option>
                                                <option value="comments" {{ request('sort') == 'comments' ? 'selected' : '' }}>Comments</option>
                                                <option value="shares" {{ request('sort') == 'shares' ? 'selected' : '' }}>Shares</option>
                                                <option value="views" {{ request('sort') == 'views' ? 'selected' : '' }}>Views</option>
                                                <option value="engagement_rate" {{ request('sort') == 'engagement_rate' ? 'selected' : '' }}>Engagement Rate</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">&nbsp;</label>
                                            <div>
                                                <button type="submit" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-filter"></i> Filter
                                                </button>
                                                <a href="{{ route('analytics.posts') }}" class="btn btn-outline-secondary btn-sm">
                                                    <i class="fas fa-times"></i> Clear
                                                </a>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Posts Performance Chart -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Posts Performance Overview</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="postsChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Insights with Top Posts -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Best Performing Platform</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="platformPerformanceChart" width="300" height="200"></canvas>
                                    
                                    <!-- Top Posts for Best Platform -->
                                    <div class="mt-4">
                                        <h6 class="mb-3">Top Posts from Best Platform</h6>
                                        @if($topPosts->count() > 0)
                                            <div class="list-group">
                                                @foreach($topPosts->take(3) as $index => $post)
                                                    <div class="list-group-item">
                                                        <div class="d-flex justify-content-between align-items-start">
                                                            <div class="flex-grow-1">
                                                                <h6 class="mb-1">{{ Str::limit($post->content, 60) }}</h6>
                                                                <small class="text-muted">
                                                                    <i class="fas fa-heart text-danger"></i> {{ number_format($post->engagement->likes ?? 0) }}
                                                                    <i class="fas fa-comment text-info ms-2"></i> {{ number_format($post->engagement->comments ?? 0) }}
                                                                    <i class="fas fa-share text-warning ms-2"></i> {{ number_format($post->engagement->shares ?? 0) }}
                                                                </small>
                                                            </div>
                                                            <span class="badge bg-primary">{{ $index + 1 }}</span>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-center py-3">
                                                <i class="fas fa-chart-line fa-2x text-muted mb-2"></i>
                                                <p class="text-muted small">No posts available</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Engagement Trends</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="engagementTrendsChart" width="400" height="200"></canvas>
                                    
                                    <!-- Top Posts by Engagement -->
                                    <div class="mt-4">
                                        <h6 class="mb-3">Top Posts by Engagement</h6>
                                        @if($topPosts->count() > 0)
                                            <div class="list-group">
                                                @foreach($topPosts->take(3) as $index => $post)
                                                    @php
                                                        $engagement = 0;
                                                        if (($post->engagement->impressions ?? 0) > 0) {
                                                            $engagement = (($post->engagement->likes ?? 0) + ($post->engagement->comments ?? 0) + ($post->engagement->shares ?? 0)) / ($post->engagement->impressions ?? 1) * 100;
                                                        }
                                                    @endphp
                                                    <div class="list-group-item">
                                                        <div class="d-flex justify-content-between align-items-start">
                                                            <div class="flex-grow-1">
                                                                <h6 class="mb-1">{{ Str::limit($post->content, 60) }}</h6>
                                                                <small class="text-muted">
                                                                    <span class="badge bg-success">{{ number_format($engagement, 1) }}% engagement</span>
                                                                    <span class="ms-2">{{ $post->created_at->format('M j, Y') }}</span>
                                                                </small>
                                                            </div>
                                                            <span class="badge bg-success">{{ $index + 1 }}</span>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-center py-3">
                                                <i class="fas fa-chart-line fa-2x text-muted mb-2"></i>
                                                <p class="text-muted small">No posts available</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Complete Top Posts List -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Complete Top Performing Posts</h6>
                                </div>
                                <div class="card-body">
                                    @if($topPosts->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Rank</th>
                                                        <th>Post Content</th>
                                                        <th>Platform</th>
                                                        <th>Likes</th>
                                                        <th>Comments</th>
                                                        <th>Shares</th>
                                                        <th>Views</th>
                                                        <th>Engagement Rate</th>
                                                        <th>Posted</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($topPosts as $index => $post)
                                                        <tr>
                                                            <td>
                                                                <span class="badge bg-primary">{{ $index + 1 }}</span>
                                                            </td>
                                                            <td>
                                                                <div>
                                                                    <strong>{{ Str::limit($post->content, 100) }}</strong>
                                                                    @if($post->campaign)
                                                                        <br><small class="text-muted">Campaign: {{ $post->campaign->name }}</small>
                                                                    @endif
                                                                </div>
                                                            </td>
                                                            <td>
                                                                @php
                                                                    $platform = $post->engagement->platform ?? $post->socialAccount->platform ?? 'unknown';
                                                                @endphp
                                                                @switch($platform)
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
                                                                    @default
                                                                        <i class="fas fa-globe text-secondary"></i> {{ ucfirst($platform) }}
                                                                @endswitch
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-success">{{ number_format($post->engagement->likes ?? 0) }}</span>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-info">{{ number_format($post->engagement->comments ?? 0) }}</span>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-warning">{{ number_format($post->engagement->shares ?? 0) }}</span>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-secondary">{{ number_format($post->engagement->impressions ?? 0) }}</span>
                                                            </td>
                                                            <td>
                                                                @php
                                                                    $engagement = 0;
                                                                    if (($post->engagement->impressions ?? 0) > 0) {
                                                                        $engagement = (($post->engagement->likes ?? 0) + ($post->engagement->comments ?? 0) + ($post->engagement->shares ?? 0)) / ($post->engagement->impressions ?? 1) * 100;
                                                                    }
                                                                @endphp
                                                                <span class="badge bg-primary">{{ number_format($engagement, 1) }}%</span>
                                                            </td>
                                                            <td>
                                                                <small class="text-muted">{{ $post->created_at->format('M j, Y') }}</small>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        
                                        <!-- Pagination -->
                                        <div class="d-flex justify-content-center mt-4">
                                            {{ $topPosts->links() }}
                                        </div>
                                    @else
                                        <div class="text-center py-5">
                                            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">No Posts Found</h5>
                                            <p class="text-muted">No posts match your current filters.</p>
                                            <a href="{{ route('analytics.posts') }}" class="btn btn-primary">
                                                <i class="fas fa-eye"></i> View All Posts
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
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Analytics posts page loaded');
    
    // Posts Performance Chart
    const postsChartEl = document.getElementById('postsChart');
    if (!postsChartEl) {
        console.error('Posts chart element not found');
        return;
    }
    const postsCtx = postsChartEl.getContext('2d');
    const postsChart = new Chart(postsCtx, {
        type: 'bar',
        data: {
            labels: ['Post 1', 'Post 2', 'Post 3', 'Post 4', 'Post 5'],
            datasets: [{
                label: 'Likes',
                data: [65, 59, 80, 81, 56],
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgb(255, 99, 132)',
                borderWidth: 1
            }, {
                label: 'Comments',
                data: [28, 48, 40, 19, 86],
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgb(54, 162, 235)',
                borderWidth: 1
            }, {
                label: 'Shares',
                data: [12, 19, 15, 25, 22],
                backgroundColor: 'rgba(255, 205, 86, 0.2)',
                borderColor: 'rgb(255, 205, 86)',
                borderWidth: 1
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
                    text: 'Top Posts Performance'
                }
            }
        }
    });

    // Platform Performance Chart
    const platformPerformanceEl = document.getElementById('platformPerformanceChart');
    if (!platformPerformanceEl) {
        console.error('Platform performance chart element not found');
        return;
    }
    const platformPerformanceCtx = platformPerformanceEl.getContext('2d');
    const platformPerformanceChart = new Chart(platformPerformanceCtx, {
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
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 15,
                        padding: 10,
                        font: {
                            size: 12
                        }
                    }
                },
                title: {
                    display: true,
                    text: 'Performance by Platform',
                    font: {
                        size: 16
                    }
                }
            }
        }
    });

    // Engagement Trends Chart
    const engagementTrendsEl = document.getElementById('engagementTrendsChart');
    if (!engagementTrendsEl) {
        console.error('Engagement trends chart element not found');
        return;
    }
    const engagementTrendsCtx = engagementTrendsEl.getContext('2d');
    const engagementTrendsChart = new Chart(engagementTrendsCtx, {
        type: 'line',
        data: {
            labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
            datasets: [{
                label: 'Engagement Rate',
                data: [2.5, 3.2, 4.1, 3.8],
                borderColor: 'rgb(75, 192, 192)',
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
                    text: 'Engagement Rate Trends'
                }
            }
        }
    });
    
    console.log('All charts initialized successfully');
});

function refreshData() {
    location.reload();
}
</script>
@endpush
@endsection 