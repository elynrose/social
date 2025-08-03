@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar"></i> Analytics Dashboard
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Key Metrics -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-thumbs-up fa-2x mb-2"></i>
                                    <h4 id="total-likes">-</h4>
                                    <p class="mb-0">Total Likes</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-comments fa-2x mb-2"></i>
                                    <h4 id="total-comments">-</h4>
                                    <p class="mb-0">Total Comments</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-share fa-2x mb-2"></i>
                                    <h4 id="total-shares">-</h4>
                                    <p class="mb-0">Total Shares</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-chart-line fa-2x mb-2"></i>
                                    <h4 id="total-posts">-</h4>
                                    <p class="mb-0">Total Posts</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Analytics Tools -->
                    <div class="row">
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 border-primary">
                                <div class="card-body text-center">
                                    <i class="fas fa-chart-pie fa-3x text-primary mb-3"></i>
                                    <h6 class="card-title">Overview</h6>
                                    <p class="card-text small text-muted">
                                        Get a comprehensive overview of your social media performance.
                                    </p>
                                    <a href="{{ route('analytics.overview') }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-eye"></i> View Overview
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 border-success">
                                <div class="card-body text-center">
                                    <i class="fas fa-list fa-3x text-success mb-3"></i>
                                    <h6 class="card-title">Top Posts</h6>
                                    <p class="card-text small text-muted">
                                        See your best performing posts and their engagement metrics.
                                    </p>
                                    <a href="{{ route('analytics.posts') }}" class="btn btn-success btn-sm">
                                        <i class="fas fa-trophy"></i> View Top Posts
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 border-info">
                                <div class="card-body text-center">
                                    <i class="fas fa-chart-line fa-3x text-info mb-3"></i>
                                    <h6 class="card-title">Engagement Analysis</h6>
                                    <p class="card-text small text-muted">
                                        Analyze engagement rates across different platforms.
                                    </p>
                                    <a href="{{ route('analytics.engagement') }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-chart-line"></i> View Engagement
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 border-warning">
                                <div class="card-body text-center">
                                    <i class="fab fa-facebook fa-3x text-warning mb-3"></i>
                                    <h6 class="card-title">Facebook Analytics</h6>
                                    <p class="card-text small text-muted">
                                        Detailed analytics for your Facebook posts and performance.
                                    </p>
                                    <a href="{{ route('analytics.platform', 'facebook') }}" class="btn btn-warning btn-sm">
                                        <i class="fab fa-facebook"></i> View Facebook
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 border-info">
                                <div class="card-body text-center">
                                    <i class="fab fa-twitter fa-3x text-info mb-3"></i>
                                    <h6 class="card-title">Twitter Analytics</h6>
                                    <p class="card-text small text-muted">
                                        Track your Twitter performance and engagement metrics.
                                    </p>
                                    <a href="{{ route('analytics.platform', 'twitter') }}" class="btn btn-info btn-sm">
                                        <i class="fab fa-twitter"></i> View Twitter
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 border-primary">
                                <div class="card-body text-center">
                                    <i class="fab fa-linkedin fa-3x text-primary mb-3"></i>
                                    <h6 class="card-title">LinkedIn Analytics</h6>
                                    <p class="card-text small text-muted">
                                        Professional network analytics and performance insights.
                                    </p>
                                    <a href="{{ route('analytics.platform', 'linkedin') }}" class="btn btn-primary btn-sm">
                                        <i class="fab fa-linkedin"></i> View LinkedIn
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity Chart -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Recent Activity</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="activityChart" width="400" height="200"></canvas>
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
    loadAnalytics();
    loadActivityChart();
});

function loadAnalytics() {
    fetch('/api/analytics/overview')
        .then(response => response.json())
        .then(data => {
            let totalLikes = 0;
            let totalComments = 0;
            let totalShares = 0;
            let totalPosts = 0;

            data.forEach(platform => {
                totalLikes += platform.likes || 0;
                totalComments += platform.comments || 0;
                totalShares += platform.shares || 0;
                totalPosts += platform.posts || 0;
            });

            document.getElementById('total-likes').textContent = totalLikes.toLocaleString();
            document.getElementById('total-comments').textContent = totalComments.toLocaleString();
            document.getElementById('total-shares').textContent = totalShares.toLocaleString();
            document.getElementById('total-posts').textContent = totalPosts.toLocaleString();
        })
        .catch(error => {
            console.error('Error loading analytics:', error);
        });
}

function loadActivityChart() {
    const ctx = document.getElementById('activityChart').getContext('2d');
    
    // Sample data - in a real app, this would come from the API
    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Engagement',
                data: [12, 19, 3, 5, 2, 3],
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}
</script>
@endpush
@endsection 