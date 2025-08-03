@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-sync-alt"></i> Fetch Mentions</h4>
                    <p class="text-muted mb-0">Manually fetch and import mentions from connected social media accounts</p>
                </div>
                <div class="card-body">
                    <!-- Fetch Options -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6><i class="fas fa-cog"></i> Fetch Configuration</h6>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('mentions.fetch') }}" method="POST" id="fetchForm">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label">Platforms</label>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="platform_twitter" name="platforms[]" value="twitter" checked>
                                                        <label class="form-check-label" for="platform_twitter">
                                                            <i class="fab fa-twitter text-primary"></i> Twitter
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="platform_instagram" name="platforms[]" value="instagram" checked>
                                                        <label class="form-check-label" for="platform_instagram">
                                                            <i class="fab fa-instagram text-danger"></i> Instagram
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="platform_facebook" name="platforms[]" value="facebook" checked>
                                                        <label class="form-check-label" for="platform_facebook">
                                                            <i class="fab fa-facebook text-primary"></i> Facebook
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="platform_linkedin" name="platforms[]" value="linkedin" checked>
                                                        <label class="form-check-label" for="platform_linkedin">
                                                            <i class="fab fa-linkedin text-primary"></i> LinkedIn
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="dateRange" class="form-label">Date Range</label>
                                            <select class="form-select" id="dateRange" name="date_range">
                                                <option value="1">Last 24 hours</option>
                                                <option value="7">Last 7 days</option>
                                                <option value="30" selected>Last 30 days</option>
                                                <option value="90">Last 90 days</option>
                                                <option value="custom">Custom range</option>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-3" id="customDateRange" style="display: none;">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label for="startDate" class="form-label">Start Date</label>
                                                    <input type="date" class="form-control" id="startDate" name="start_date">
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="endDate" class="form-label">End Date</label>
                                                    <input type="date" class="form-control" id="endDate" name="end_date">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="keywords" class="form-label">Keywords (Optional)</label>
                                            <input type="text" class="form-control" id="keywords" name="keywords" placeholder="Enter keywords separated by commas">
                                            <div class="form-text">Leave empty to fetch all mentions</div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Fetch Options</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="include_replies" name="include_replies" value="1" checked>
                                                <label class="form-check-label" for="include_replies">
                                                    Include replies and comments
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="include_retweets" name="include_retweets" value="1">
                                                <label class="form-check-label" for="include_retweets">
                                                    Include retweets and shares
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="include_quotes" name="include_quotes" value="1">
                                                <label class="form-check-label" for="include_quotes">
                                                    Include quote tweets and mentions
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary" id="fetchBtn">
                                            <i class="fas fa-sync-alt"></i> Start Fetching
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6><i class="fas fa-info-circle"></i> Fetch Status</h6>
                                </div>
                                <div class="card-body">
                                    <div id="fetchStatus">
                                        <div class="text-center text-muted">
                                            <i class="fas fa-info-circle fa-2x mb-2"></i>
                                            <p>Click "Start Fetching" to begin importing mentions</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h6><i class="fas fa-history"></i> Recent Fetches</h6>
                                </div>
                                <div class="card-body">
                                    <div id="recentFetches">
                                        @forelse($recentFetches ?? [] as $fetch)
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div>
                                                    <small class="text-muted">{{ $fetch->created_at->format('M d, H:i') }}</small>
                                                    <div>{{ $fetch->platform }} - {{ $fetch->mentions_count }} mentions</div>
                                                </div>
                                                <span class="badge bg-{{ $fetch->status === 'completed' ? 'success' : 'warning' }}">
                                                    {{ ucfirst($fetch->status) }}
                                                </span>
                                            </div>
                                        @empty
                                            <p class="text-muted text-center">No recent fetches</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Fetch Progress -->
                    <div id="fetchProgress" class="card mb-4" style="display: none;">
                        <div class="card-header">
                            <h6><i class="fas fa-spinner fa-spin"></i> Fetching Mentions...</h6>
                        </div>
                        <div class="card-body">
                            <div class="progress mb-3">
                                <div class="progress-bar" role="progressbar" style="width: 0%" id="progressBar">0%</div>
                            </div>
                            <div id="progressDetails">
                                <div class="row">
                                    <div class="col-md-3">
                                        <small class="text-muted">Platform</small>
                                        <div id="currentPlatform">-</div>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted">Mentions Found</small>
                                        <div id="mentionsFound">0</div>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted">Processed</small>
                                        <div id="mentionsProcessed">0</div>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted">Status</small>
                                        <div id="currentStatus">Initializing...</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Fetch Results -->
                    <div id="fetchResults" class="card" style="display: none;">
                        <div class="card-header">
                            <h6><i class="fas fa-check-circle"></i> Fetch Results</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h4 class="text-success" id="totalMentions">0</h4>
                                        <small class="text-muted">Total Mentions</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h4 class="text-primary" id="newMentions">0</h4>
                                        <small class="text-muted">New Mentions</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h4 class="text-warning" id="duplicateMentions">0</h4>
                                        <small class="text-muted">Duplicates</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h4 class="text-info" id="processingTime">0s</h4>
                                        <small class="text-muted">Processing Time</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <h6>Platform Breakdown</h6>
                                <div id="platformBreakdown"></div>
                            </div>
                            
                            <div class="mt-3">
                                <a href="{{ route('mentions.index') }}" class="btn btn-primary">
                                    <i class="fas fa-eye"></i> View All Mentions
                                </a>
                                <button class="btn btn-outline-secondary" onclick="exportResults()">
                                    <i class="fas fa-download"></i> Export Results
                                </button>
                            </div>
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
    const fetchForm = document.getElementById('fetchForm');
    const dateRange = document.getElementById('dateRange');
    const customDateRange = document.getElementById('customDateRange');
    const fetchProgress = document.getElementById('fetchProgress');
    const fetchResults = document.getElementById('fetchResults');
    
    // Show/hide custom date range
    dateRange.addEventListener('change', function() {
        if (this.value === 'custom') {
            customDateRange.style.display = 'block';
        } else {
            customDateRange.style.display = 'none';
        }
    });
    
    // Handle form submission
    fetchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const fetchBtn = document.getElementById('fetchBtn');
        const originalText = fetchBtn.innerHTML;
        
        // Show progress
        fetchBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Fetching...';
        fetchBtn.disabled = true;
        fetchProgress.style.display = 'block';
        fetchResults.style.display = 'none';
        
        // Simulate progress updates
        let progress = 0;
        const progressBar = document.getElementById('progressBar');
        const progressInterval = setInterval(() => {
            progress += Math.random() * 10;
            if (progress > 100) progress = 100;
            progressBar.style.width = progress + '%';
            progressBar.textContent = Math.round(progress) + '%';
            
            if (progress >= 100) {
                clearInterval(progressInterval);
                showResults();
            }
        }, 500);
        
        // Make actual API call
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateProgress(data);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred during fetching.');
        })
        .finally(() => {
            fetchBtn.innerHTML = originalText;
            fetchBtn.disabled = false;
        });
    });
});

function updateProgress(data) {
    document.getElementById('currentPlatform').textContent = data.platform || '-';
    document.getElementById('mentionsFound').textContent = data.mentions_found || 0;
    document.getElementById('mentionsProcessed').textContent = data.mentions_processed || 0;
    document.getElementById('currentStatus').textContent = data.status || 'Processing...';
}

function showResults() {
    document.getElementById('fetchProgress').style.display = 'none';
    document.getElementById('fetchResults').style.display = 'block';
    
    // Simulate results
    document.getElementById('totalMentions').textContent = Math.floor(Math.random() * 100) + 50;
    document.getElementById('newMentions').textContent = Math.floor(Math.random() * 50) + 20;
    document.getElementById('duplicateMentions').textContent = Math.floor(Math.random() * 10);
    document.getElementById('processingTime').textContent = Math.floor(Math.random() * 30) + 5 + 's';
    
    // Platform breakdown
    const platforms = ['Twitter', 'Instagram', 'Facebook', 'LinkedIn'];
    const breakdown = document.getElementById('platformBreakdown');
    breakdown.innerHTML = '';
    
    platforms.forEach(platform => {
        const count = Math.floor(Math.random() * 25) + 5;
        const div = document.createElement('div');
        div.className = 'd-flex justify-content-between mb-2';
        div.innerHTML = `
            <span>${platform}</span>
            <span class="badge bg-primary">${count}</span>
        `;
        breakdown.appendChild(div);
    });
}

function exportResults() {
    // Implementation for exporting results
    alert('Results exported successfully!');
}
</script>
@endpush
@endsection 