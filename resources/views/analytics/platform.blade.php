@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        @switch($platform)
                            @case('facebook')
                                <i class="fab fa-facebook text-primary"></i> Facebook Analytics
                                @break
                            @case('twitter')
                                <i class="fab fa-twitter text-info"></i> Twitter Analytics
                                @break
                            @case('linkedin')
                                <i class="fab fa-linkedin text-primary"></i> LinkedIn Analytics
                                @break
                            @case('instagram')
                                <i class="fab fa-instagram text-danger"></i> Instagram Analytics
                                @break
                            @default
                                <i class="fas fa-chart-line"></i> {{ ucfirst($platform) }} Analytics
                        @endswitch
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

                    <!-- Platform-specific Metrics -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-users fa-2x mb-2"></i>
                                    <h4>{{ number_format($platformStats['followers'] ?? 0) }}</h4>
                                    <p class="mb-0">Followers</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-eye fa-2x mb-2"></i>
                                    <h4>{{ number_format($platformStats['impressions'] ?? 0) }}</h4>
                                    <p class="mb-0">Impressions</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-heart fa-2x mb-2"></i>
                                    <h4>{{ number_format($platformStats['total_engagement'] ?? 0) }}</h4>
                                    <p class="mb-0">Total Engagement</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-percentage fa-2x mb-2"></i>
                                    <h4>{{ number_format($platformStats['engagement_rate'] ?? 0, 1) }}%</h4>
                                    <p class="mb-0">Engagement Rate</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Platform-specific Charts -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">{{ ucfirst($platform) }} Performance Over Time</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="platformPerformanceChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Content Distribution</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="contentDistributionChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Platform-specific Insights -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Best Performing Content</h6>
                                </div>
                                <div class="card-body">
                                    @if(isset($platformStats['top_content']) && count($platformStats['top_content']) > 0)
                                        <div class="list-group list-group-flush">
                                            @foreach($platformStats['top_content'] as $content)
                                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <strong>{{ Str::limit($content['title'] ?? 'Content', 50) }}</strong>
                                                        <br><small class="text-muted">{{ $content['type'] ?? 'Unknown Type' }}</small>
                                                    </div>
                                                    <div class="text-end">
                                                        <span class="badge bg-primary">{{ number_format($content['engagement'] ?? 0) }}</span>
                                                        <br><small class="text-muted">{{ $content['date'] ?? 'Unknown Date' }}</small>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-muted text-center">No content data available</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Optimal Posting Times</h6>
                                </div>
                                <div class="card-body">
                                    @if(isset($platformStats['optimal_times']) && count($platformStats['optimal_times']) > 0)
                                        <div class="list-group list-group-flush">
                                            @foreach($platformStats['optimal_times'] as $time)
                                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <strong>{{ $time['day'] ?? 'Day' }}</strong>
                                                        <br><small class="text-muted">{{ $time['time'] ?? 'Time' }}</small>
                                                    </div>
                                                    <span class="badge bg-success">{{ number_format($time['engagement'] ?? 0, 1) }}%</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-muted text-center">No timing data available</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Platform-specific Recommendations -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">{{ ucfirst($platform) }} Optimization Recommendations</h6>
                                </div>
                                <div class="card-body">
                                    @if(isset($platformStats['recommendations']) && count($platformStats['recommendations']) > 0)
                                        <div class="row">
                                            @foreach($platformStats['recommendations'] as $recommendation)
                                                <div class="col-md-4 mb-3">
                                                    <div class="card border-primary">
                                                        <div class="card-body">
                                                            <h6 class="card-title">
                                                                <i class="fas fa-lightbulb text-warning"></i>
                                                                {{ $recommendation['title'] ?? 'Recommendation' }}
                                                            </h6>
                                                            <p class="card-text small">{{ $recommendation['description'] ?? 'No description available' }}</p>
                                                            @if(isset($recommendation['impact']))
                                                                <small class="text-muted">Potential Impact: {{ $recommendation['impact'] }}</small>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-muted text-center">No recommendations available for {{ ucfirst($platform) }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Platform Navigation -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Other Platform Analytics</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <a href="{{ route('analytics.platform', 'facebook') }}" 
                                               class="btn btn-outline-primary w-100 mb-2 {{ $platform == 'facebook' ? 'active' : '' }}">
                                                <i class="fab fa-facebook"></i> Facebook
                                            </a>
                                        </div>
                                        <div class="col-md-3">
                                            <a href="{{ route('analytics.platform', 'twitter') }}" 
                                               class="btn btn-outline-info w-100 mb-2 {{ $platform == 'twitter' ? 'active' : '' }}">
                                                <i class="fab fa-twitter"></i> Twitter
                                            </a>
                                        </div>
                                        <div class="col-md-3">
                                            <a href="{{ route('analytics.platform', 'linkedin') }}" 
                                               class="btn btn-outline-primary w-100 mb-2 {{ $platform == 'linkedin' ? 'active' : '' }}">
                                                <i class="fab fa-linkedin"></i> LinkedIn
                                            </a>
                                        </div>
                                        <div class="col-md-3">
                                            <a href="{{ route('analytics.platform', 'instagram') }}" 
                                               class="btn btn-outline-danger w-100 mb-2 {{ $platform == 'instagram' ? 'active' : '' }}">
                                                <i class="fab fa-instagram"></i> Instagram
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
    // Platform Performance Chart
    const platformPerformanceCtx = document.getElementById('platformPerformanceChart').getContext('2d');
    const platformPerformanceChart = new Chart(platformPerformanceCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Followers',
                data: [1200, 1350, 1500, 1650, 1800, 1950],
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }, {
                label: 'Engagement',
                data: [45, 52, 48, 61, 55, 67],
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                tension: 0.1
            }, {
                label: 'Impressions',
                data: [5000, 5500, 6000, 6500, 7000, 7500],
                borderColor: 'rgb(255, 205, 86)',
                backgroundColor: 'rgba(255, 205, 86, 0.2)',
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
                    text: '{{ ucfirst($platform) }} Performance Trends'
                }
            }
        }
    });

    // Content Distribution Chart
    const contentDistributionCtx = document.getElementById('contentDistributionChart').getContext('2d');
    const contentDistributionChart = new Chart(contentDistributionCtx, {
        type: 'doughnut',
        data: {
            labels: ['Images', 'Videos', 'Text', 'Stories'],
            datasets: [{
                data: [40, 30, 20, 10],
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
                    text: 'Content Distribution'
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