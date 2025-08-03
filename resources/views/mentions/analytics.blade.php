@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-chart-line"></i> Mentions Analytics</h4>
                    <p class="text-muted mb-0">Analyze mention trends and performance</p>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label for="dateRange" class="form-label">Date Range</label>
                            <select class="form-select" id="dateRange">
                                <option value="7">Last 7 days</option>
                                <option value="30" selected>Last 30 days</option>
                                <option value="90">Last 90 days</option>
                                <option value="365">Last year</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="platformFilter" class="form-label">Platform</label>
                            <select class="form-select" id="platformFilter">
                                <option value="">All Platforms</option>
                                <option value="twitter">Twitter</option>
                                <option value="instagram">Instagram</option>
                                <option value="facebook">Facebook</option>
                                <option value="linkedin">LinkedIn</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="sentimentFilter" class="form-label">Sentiment</label>
                            <select class="form-select" id="sentimentFilter">
                                <option value="">All Sentiments</option>
                                <option value="positive">Positive</option>
                                <option value="neutral">Neutral</option>
                                <option value="negative">Negative</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="statusFilter" class="form-label">Status</label>
                            <select class="form-select" id="statusFilter">
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                    </div>

                    <!-- Key Metrics -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Total Mentions</h6>
                                            <h3 class="mb-0">{{ number_format($totalMentions ?? 0) }}</h3>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-at fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Positive Mentions</h6>
                                            <h3 class="mb-0">{{ number_format($positiveMentions ?? 0) }}</h3>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-smile fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Neutral Mentions</h6>
                                            <h3 class="mb-0">{{ number_format($neutralMentions ?? 0) }}</h3>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-meh fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Negative Mentions</h6>
                                            <h3 class="mb-0">{{ number_format($negativeMentions ?? 0) }}</h3>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-frown fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Row -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h6><i class="fas fa-chart-line"></i> Mentions Over Time</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="mentionsChart" height="300"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6><i class="fas fa-chart-pie"></i> Platform Distribution</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="platformChart" height="300"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sentiment and Engagement -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6><i class="fas fa-heart"></i> Sentiment Analysis</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="sentimentChart" height="250"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6><i class="fas fa-users"></i> Engagement Metrics</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="text-center mb-3">
                                                <h4 class="text-primary">{{ number_format($avgEngagement ?? 0) }}</h4>
                                                <small class="text-muted">Avg Engagement</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-center mb-3">
                                                <h4 class="text-success">{{ number_format($totalReach ?? 0) }}</h4>
                                                <small class="text-muted">Total Reach</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-center mb-3">
                                                <h4 class="text-info">{{ number_format($avgInfluence ?? 0) }}</h4>
                                                <small class="text-muted">Avg Influence</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-center mb-3">
                                                <h4 class="text-warning">{{ number_format($responseRate ?? 0) }}%</h4>
                                                <small class="text-muted">Response Rate</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top Mentions and Influencers -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6><i class="fas fa-star"></i> Top Mentions</h6>
                                </div>
                                <div class="card-body">
                                    <div id="topMentionsList">
                                        @forelse($topMentions ?? [] as $mention)
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="avatar me-3">
                                                    <i class="fab fa-{{ strtolower($mention->platform) }} fa-lg text-primary"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-0">{{ Str::limit($mention->content, 50) }}</h6>
                                                    <small class="text-muted">@{{ $mention->author_username }} • {{ $mention->created_at->format('M d') }}</small>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-success">{{ $mention->engagement_score }}</span>
                                                </div>
                                            </div>
                                        @empty
                                            <p class="text-muted text-center">No top mentions available</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6><i class="fas fa-user-friends"></i> Top Influencers</h6>
                                </div>
                                <div class="card-body">
                                    <div id="topInfluencersList">
                                        @forelse($topInfluencers ?? [] as $influencer)
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="avatar me-3">
                                                    @if($influencer->avatar)
                                                        <img src="{{ $influencer->avatar }}" alt="Avatar" class="rounded-circle" width="40" height="40">
                                                    @else
                                                        <i class="fas fa-user-circle fa-2x text-muted"></i>
                                                    @endif
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-0">{{ $influencer->name }}</h6>
                                                    <small class="text-muted">@{{ $influencer->username }} • {{ number_format($influencer->followers) }} followers</small>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-info">{{ $influencer->mention_count }} mentions</span>
                                                </div>
                                            </div>
                                        @empty
                                            <p class="text-muted text-center">No top influencers available</p>
                                        @endforelse
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
    // Mentions Over Time Chart
    const mentionsCtx = document.getElementById('mentionsChart').getContext('2d');
    const mentionsChart = new Chart(mentionsCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Total Mentions',
                data: [12, 19, 3, 5, 2, 3],
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }, {
                label: 'Positive Mentions',
                data: [8, 12, 2, 3, 1, 2],
                borderColor: 'rgb(75, 192, 75)',
                tension: 0.1
            }, {
                label: 'Negative Mentions',
                data: [2, 3, 1, 1, 0, 1],
                borderColor: 'rgb(192, 75, 75)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Platform Distribution Chart
    const platformCtx = document.getElementById('platformChart').getContext('2d');
    const platformChart = new Chart(platformCtx, {
        type: 'doughnut',
        data: {
            labels: ['Twitter', 'Instagram', 'Facebook', 'LinkedIn'],
            datasets: [{
                data: [30, 25, 20, 25],
                backgroundColor: [
                    'rgb(29, 161, 242)',
                    'rgb(225, 48, 108)',
                    'rgb(66, 103, 178)',
                    'rgb(0, 119, 181)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Sentiment Analysis Chart
    const sentimentCtx = document.getElementById('sentimentChart').getContext('2d');
    const sentimentChart = new Chart(sentimentCtx, {
        type: 'bar',
        data: {
            labels: ['Positive', 'Neutral', 'Negative'],
            datasets: [{
                label: 'Mentions',
                data: [65, 25, 10],
                backgroundColor: [
                    'rgba(75, 192, 75, 0.8)',
                    'rgba(255, 205, 86, 0.8)',
                    'rgba(255, 99, 132, 0.8)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Filter functionality
    const filters = ['dateRange', 'platformFilter', 'sentimentFilter', 'statusFilter'];
    filters.forEach(filterId => {
        document.getElementById(filterId).addEventListener('change', updateCharts);
    });

    function updateCharts() {
        // Implementation for updating charts based on filters
        console.log('Updating charts with new filters...');
    }
});
</script>
@endpush
@endsection 