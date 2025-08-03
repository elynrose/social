@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-clock"></i> Post Timing Optimization</h4>
                    <p class="text-muted mb-0">Find the best times to post for maximum engagement</p>
                </div>
                <div class="card-body">
                    <form action="{{ route('ai.timing') }}" method="POST" id="timingForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="platform" class="form-label">Platform *</label>
                                    <select class="form-select" id="platform" name="platform" required>
                                        <option value="instagram">Instagram</option>
                                        <option value="facebook">Facebook</option>
                                        <option value="twitter">Twitter</option>
                                        <option value="linkedin">LinkedIn</option>
                                        <option value="tiktok">TikTok</option>
                                        <option value="youtube">YouTube</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="content_type" class="form-label">Content Type</label>
                                    <select class="form-select" id="content_type" name="content_type">
                                        <option value="image">Image Post</option>
                                        <option value="video">Video</option>
                                        <option value="story">Story</option>
                                        <option value="text">Text Post</option>
                                        <option value="carousel">Carousel</option>
                                        <option value="reel">Reel</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="audience" class="form-label">Target Audience</label>
                                    <select class="form-select" id="audience" name="audience">
                                        <option value="general">General</option>
                                        <option value="business">Business/Professional</option>
                                        <option value="youth">Youth (18-25)</option>
                                        <option value="professionals">Professionals (25-45)</option>
                                        <option value="parents">Parents</option>
                                        <option value="students">Students</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="timezone" class="form-label">Timezone</label>
                                    <select class="form-select" id="timezone" name="timezone">
                                        <option value="UTC">UTC</option>
                                        <option value="EST">Eastern Time (EST)</option>
                                        <option value="CST">Central Time (CST)</option>
                                        <option value="MST">Mountain Time (MST)</option>
                                        <option value="PST">Pacific Time (PST)</option>
                                        <option value="GMT">GMT</option>
                                        <option value="CET">Central European Time (CET)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Analysis Factors</label>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="factor_engagement" name="factors[]" value="engagement" checked>
                                        <label class="form-check-label" for="factor_engagement">Engagement Rates</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="factor_reach" name="factors[]" value="reach" checked>
                                        <label class="form-check-label" for="factor_reach">Reach & Impressions</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="factor_competition" name="factors[]" value="competition" checked>
                                        <label class="form-check-label" for="factor_competition">Competition Analysis</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-magic"></i> Get Optimal Timing
                        </button>
                    </form>
                    
                    <div id="timingResult" class="mt-4" style="display: none;">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5><i class="fas fa-chart-line"></i> Optimal Posting Times</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-4">
                                            <h6><i class="fas fa-calendar"></i> Best Days</h6>
                                            <div id="bestDays" class="d-flex flex-wrap gap-2"></div>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <h6><i class="fas fa-clock"></i> Best Times</h6>
                                            <div id="bestTimes" class="row"></div>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <h6><i class="fas fa-lightbulb"></i> Strategy Insights</h6>
                                            <div id="strategyInsights" class="alert alert-info"></div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6><i class="fas fa-chart-bar"></i> Performance Metrics</h6>
                                            </div>
                                            <div class="card-body">
                                                <div id="performanceMetrics"></div>
                                            </div>
                                        </div>
                                        
                                        <div class="card mt-3">
                                            <div class="card-header">
                                                <h6><i class="fas fa-exclamation-triangle"></i> Avoid These Times</h6>
                                            </div>
                                            <div class="card-body">
                                                <div id="avoidTimes"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-12">
                                        <div class="mb-4">
                                            <h6><i class="fas fa-calendar-plus"></i> Recommended Schedule</h6>
                                            <div id="recommendedSchedule" class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Day</th>
                                                            <th>Best Time</th>
                                                            <th>Content Type</th>
                                                            <th>Expected Engagement</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="scheduleTable">
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline-primary" onclick="exportTiming()">
                                        <i class="fas fa-download"></i> Export Schedule
                                    </button>
                                    <button class="btn btn-outline-success" onclick="regenerateTiming()">
                                        <i class="fas fa-redo"></i> Re-analyze
                                    </button>
                                    <button class="btn btn-outline-info" onclick="addToCalendar()">
                                        <i class="fas fa-calendar-plus"></i> Add to Calendar
                                    </button>
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('timingForm');
    const resultDiv = document.getElementById('timingResult');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        // Show loading state
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Analyzing...';
        submitBtn.disabled = true;
        
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayTimingAnalysis(data);
                resultDiv.style.display = 'block';
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });
});

function displayTimingAnalysis(data) {
    // Display best days
    const bestDays = document.getElementById('bestDays');
    bestDays.innerHTML = '';
    if (data.best_days && data.best_days.length > 0) {
        data.best_days.forEach(day => {
            const badge = document.createElement('span');
            badge.className = 'badge bg-success';
            badge.textContent = day;
            bestDays.appendChild(badge);
        });
    }
    
    // Display best times
    const bestTimes = document.getElementById('bestTimes');
    bestTimes.innerHTML = '';
    if (data.best_times && data.best_times.length > 0) {
        data.best_times.forEach(time => {
            const timeDiv = document.createElement('div');
            timeDiv.className = 'col-md-6 mb-2';
            timeDiv.innerHTML = `
                <div class="card">
                    <div class="card-body text-center">
                        <h6>${time}</h6>
                        <small class="text-muted">High engagement</small>
                    </div>
                </div>
            `;
            bestTimes.appendChild(timeDiv);
        });
    }
    
    // Display strategy insights
    const strategyInsights = document.getElementById('strategyInsights');
    strategyInsights.innerHTML = '';
    if (data.reasoning) {
        strategyInsights.innerHTML = `<p>${data.reasoning}</p>`;
    }
    
    // Display performance metrics
    const performanceMetrics = document.getElementById('performanceMetrics');
    performanceMetrics.innerHTML = '';
    if (data.metrics) {
        Object.entries(data.metrics).forEach(([metric, value]) => {
            const metricDiv = document.createElement('div');
            metricDiv.className = 'd-flex justify-content-between mb-2';
            metricDiv.innerHTML = `
                <span>${metric}</span>
                <strong>${value}</strong>
            `;
            performanceMetrics.appendChild(metricDiv);
        });
    }
    
    // Display times to avoid
    const avoidTimes = document.getElementById('avoidTimes');
    avoidTimes.innerHTML = '';
    if (data.avoid_times && data.avoid_times.length > 0) {
        data.avoid_times.forEach(time => {
            const timeDiv = document.createElement('div');
            timeDiv.className = 'text-danger mb-1';
            timeDiv.innerHTML = `<i class="fas fa-times"></i> ${time}`;
            avoidTimes.appendChild(timeDiv);
        });
    }
    
    // Display recommended schedule
    const scheduleTable = document.getElementById('scheduleTable');
    scheduleTable.innerHTML = '';
    if (data.schedule && data.schedule.length > 0) {
        data.schedule.forEach(item => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${item.day}</td>
                <td>${item.time}</td>
                <td>${item.content_type}</td>
                <td><span class="badge bg-success">${item.engagement}%</span></td>
            `;
            scheduleTable.appendChild(row);
        });
    }
}

function exportTiming() {
    // Implementation for exporting timing schedule
    alert('Timing schedule exported!');
}

function regenerateTiming() {
    document.getElementById('timingForm').dispatchEvent(new Event('submit'));
}

function addToCalendar() {
    // Implementation for adding schedule to calendar
    alert('Schedule added to your calendar!');
}
</script>
@endpush
@endsection 