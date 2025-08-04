@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar"></i> Engagement Analysis
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

                    <!-- Engagement Metrics -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-percentage fa-2x mb-2"></i>
                                    <h4>{{ number_format($engagementData['avg_engagement_rate'] ?? 0, 1) }}%</h4>
                                    <p class="mb-0">Avg Engagement Rate</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-users fa-2x mb-2"></i>
                                    <h4>{{ number_format($engagementData['total_reach'] ?? 0) }}</h4>
                                    <p class="mb-0">Total Reach</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-clock fa-2x mb-2"></i>
                                    <h4>{{ $engagementData['avg_response_time'] ?? '0' }}h</h4>
                                    <p class="mb-0">Avg Response Time</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-chart-line fa-2x mb-2"></i>
                                    <h4>{{ number_format($engagementData['growth_rate'] ?? 0, 1) }}%</h4>
                                    <p class="mb-0">Growth Rate</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Engagement Charts -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Engagement Trends Over Time</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="engagementTrendsChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Engagement by Type</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="engagementTypeChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Platform Comparison -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Platform Engagement Comparison</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Platform</th>
                                                    <th>Posts</th>
                                                    <th>Total Engagement</th>
                                                    <th>Avg Engagement Rate</th>
                                                    <th>Best Time to Post</th>
                                                    <th>Top Content Type</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if(isset($engagementData['platform_comparison']))
                                                    @foreach($engagementData['platform_comparison'] as $platform)
                                                        <tr>
                                                            <td>
                                                                @switch($platform['name'])
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
                                                                        <i class="fas fa-globe text-secondary"></i> {{ ucfirst($platform['name']) }}
                                                                @endswitch
                                                            </td>
                                                            <td>{{ number_format($platform['posts'] ?? 0) }}</td>
                                                            <td>{{ number_format($platform['total_engagement'] ?? 0) }}</td>
                                                            <td>{{ number_format($platform['avg_engagement_rate'] ?? 0, 1) }}%</td>
                                                            <td>{{ $platform['best_time'] ?? 'N/A' }}</td>
                                                            <td>{{ $platform['top_content_type'] ?? 'N/A' }}</td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td colspan="6" class="text-center text-muted">No platform data available</td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Engagement Insights -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Top Engagement Drivers</h6>
                                </div>
                                <div class="card-body">
                                    @if(isset($engagementData['drivers']) && count($engagementData['drivers']) > 0)
                                        <div class="list-group list-group-flush">
                                            @foreach($engagementData['drivers'] as $driver)
                                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <strong>{{ $driver['factor'] ?? 'Factor' }}</strong>
                                                        <br><small class="text-muted">{{ $driver['description'] ?? 'No description' }}</small>
                                                    </div>
                                                    <span class="badge bg-primary">{{ number_format($driver['impact'] ?? 0, 1) }}%</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-muted text-center">No engagement drivers data available</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Engagement Recommendations</h6>
                                </div>
                                <div class="card-body">
                                    @if(isset($engagementData['recommendations']) && count($engagementData['recommendations']) > 0)
                                        <div class="list-group list-group-flush">
                                            @foreach($engagementData['recommendations'] as $recommendation)
                                                <div class="list-group-item">
                                                    <div class="d-flex align-items-start">
                                                        <i class="fas fa-lightbulb text-warning me-2 mt-1"></i>
                                                        <div>
                                                            <strong>{{ $recommendation['title'] ?? 'Recommendation' }}</strong>
                                                            <br><small class="text-muted">{{ $recommendation['description'] ?? 'No description' }}</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-muted text-center">No recommendations available</p>
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
    // Engagement Trends Chart
    const engagementTrendsCtx = document.getElementById('engagementTrendsChart').getContext('2d');
    const engagementTrendsChart = new Chart(engagementTrendsCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Engagement Rate',
                data: [2.5, 3.2, 4.1, 3.8, 4.5, 5.2],
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }, {
                label: 'Reach',
                data: [1000, 1200, 1400, 1300, 1600, 1800],
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
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

    // Engagement Type Chart
    const engagementTypeCtx = document.getElementById('engagementTypeChart').getContext('2d');
    const engagementTypeChart = new Chart(engagementTypeCtx, {
        type: 'doughnut',
        data: {
            labels: ['Likes', 'Comments', 'Shares', 'Saves'],
            datasets: [{
                data: [65, 20, 10, 5],
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
                    text: 'Engagement Distribution'
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