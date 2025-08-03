@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Analytics</h1>
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <p class="mb-3">Gain insight into how your content performs across all connected platforms. Use these charts to identify trends and optimise your strategy.</p>
            <canvas id="engagementChart" height="200"></canvas>
        </div>
    </div>
    <div class="alert alert-info">Analytics data will update in real time as your posts generate engagement.</div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', async function() {
    const ctx = document.getElementById('engagementChart').getContext('2d');
    try {
        const resp = await fetch('/api/analytics', {
            headers: { 'Accept': 'application/json' }
        });
        const data = await resp.json();
        const labels = data.map(item => item.platform);
        const likes = data.map(item => item.likes);
        const comments = data.map(item => item.comments);
        const shares = data.map(item => item.shares);
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    { label: 'Likes', data: likes, backgroundColor: '#0d6efd' },
                    { label: 'Comments', data: comments, backgroundColor: '#20c997' },
                    { label: 'Shares', data: shares, backgroundColor: '#ffc107' }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    } catch (e) {
        console.error('Failed to load analytics', e);
    }
});
</script>
@endpush
@endsection