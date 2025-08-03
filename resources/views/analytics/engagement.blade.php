@extends('layouts.app')

@section('title', 'Engagement Analytics')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Engagement Analytics</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('analytics.index') }}">Analytics</a></li>
                        <li class="breadcrumb-item active">Engagement</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Platform Engagement Overview</h5>
                </div>
                <div class="card-body">
                    @if($engagementData->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Platform</th>
                                        <th>Average Likes</th>
                                        <th>Average Comments</th>
                                        <th>Average Shares</th>
                                        <th>Total Posts</th>
                                        <th>Engagement Rate</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($engagementData as $data)
                                        @php
                                            $totalEngagement = $data->avg_likes + $data->avg_comments + $data->avg_shares;
                                            $engagementRate = $data->total_posts > 0 ? round(($totalEngagement / $data->total_posts) * 100, 2) : 0;
                                        @endphp
                                        <tr>
                                            <td>
                                                <span class="badge bg-primary">{{ ucfirst($data->platform) }}</span>
                                            </td>
                                            <td>{{ number_format($data->avg_likes, 1) }}</td>
                                            <td>{{ number_format($data->avg_comments, 1) }}</td>
                                            <td>{{ number_format($data->avg_shares, 1) }}</td>
                                            <td>{{ $data->total_posts }}</td>
                                            <td>
                                                <span class="badge bg-{{ $engagementRate > 5 ? 'success' : ($engagementRate > 2 ? 'warning' : 'danger') }}">
                                                    {{ $engagementRate }}%
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No engagement data available</h5>
                            <p class="text-muted">Start posting content to see engagement analytics.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Engagement Trends</h5>
                </div>
                <div class="card-body">
                    <canvas id="engagementChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Platform Performance</h5>
                </div>
                <div class="card-body">
                    <canvas id="platformChart" width="400" height="200"></canvas>
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
    new Chart(engagementCtx, {
        type: 'line',
        data: {
            labels: @json($engagementData->pluck('platform')),
            datasets: [{
                label: 'Average Likes',
                data: @json($engagementData->pluck('avg_likes')),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }, {
                label: 'Average Comments',
                data: @json($engagementData->pluck('avg_comments')),
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                tension: 0.1
            }, {
                label: 'Average Shares',
                data: @json($engagementData->pluck('avg_shares')),
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
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

    // Platform Performance Chart
    const platformCtx = document.getElementById('platformChart').getContext('2d');
    new Chart(platformCtx, {
        type: 'doughnut',
        data: {
            labels: @json($engagementData->pluck('platform')),
            datasets: [{
                data: @json($engagementData->map(function($data) {
                    return $data->avg_likes + $data->avg_comments + $data->avg_shares;
                })),
                backgroundColor: [
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 205, 86, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(153, 102, 255, 0.8)'
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
});
</script>
@endpush
@endsection 