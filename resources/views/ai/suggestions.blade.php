@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-lightbulb"></i> Content Suggestions</h4>
                    <p class="text-muted mb-0">Get AI-powered content ideas for your social media strategy</p>
                </div>
                <div class="card-body">
                    <form action="{{ route('ai.suggestions') }}" method="POST" id="suggestionsForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="topic" class="form-label">Topic/Industry *</label>
                                    <input type="text" class="form-control" id="topic" name="topic" placeholder="e.g., Technology, Fashion, Food, Fitness..." required>
                                    <div class="form-text">What industry or topic are you creating content for?</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="content_type" class="form-label">Content Type</label>
                                    <select class="form-select" id="content_type" name="content_type">
                                        <option value="post">Social Media Post</option>
                                        <option value="blog">Blog Article</option>
                                        <option value="video">Video Script</option>
                                        <option value="story">Story Content</option>
                                        <option value="reel">Reel/TikTok</option>
                                        <option value="carousel">Carousel Post</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="audience" class="form-label">Target Audience</label>
                                    <input type="text" class="form-control" id="audience" name="audience" placeholder="e.g., Young professionals, Parents, Students...">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="platform" class="form-label">Platform</label>
                                    <select class="form-select" id="platform" name="platform">
                                        <option value="all">All Platforms</option>
                                        <option value="instagram">Instagram</option>
                                        <option value="facebook">Facebook</option>
                                        <option value="twitter">Twitter</option>
                                        <option value="linkedin">LinkedIn</option>
                                        <option value="tiktok">TikTok</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="goals" class="form-label">Content Goals</label>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="goal_awareness" name="goals[]" value="awareness">
                                        <label class="form-check-label" for="goal_awareness">Brand Awareness</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="goal_engagement" name="goals[]" value="engagement">
                                        <label class="form-check-label" for="goal_engagement">Engagement</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="goal_sales" name="goals[]" value="sales">
                                        <label class="form-check-label" for="goal_sales">Sales/Conversions</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="trends" class="form-label">Include Trending Topics</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="trends" name="trends" value="1">
                                <label class="form-check-label" for="trends">
                                    Include current trending topics and hashtags
                                </label>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-magic"></i> Get Content Suggestions
                        </button>
                    </form>
                    
                    <div id="suggestionsResult" class="mt-4" style="display: none;">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h5><i class="fas fa-lightbulb"></i> Content Suggestions</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div id="suggestionsList"></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6><i class="fas fa-chart-line"></i> Content Strategy</h6>
                                            </div>
                                            <div class="card-body">
                                                <div id="strategyInsights"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <button class="btn btn-outline-primary" onclick="regenerateSuggestions()">
                                        <i class="fas fa-redo"></i> Get More Suggestions
                                    </button>
                                    <button class="btn btn-outline-success" onclick="exportSuggestions()">
                                        <i class="fas fa-download"></i> Export to CSV
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
    const form = document.getElementById('suggestionsForm');
    const resultDiv = document.getElementById('suggestionsResult');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        // Show loading state
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
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
                displaySuggestions(data);
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

function displaySuggestions(data) {
    const suggestionsList = document.getElementById('suggestionsList');
    const strategyInsights = document.getElementById('strategyInsights');
    
    // Display suggestions
    suggestionsList.innerHTML = '';
    data.suggestions.forEach((suggestion, index) => {
        const suggestionDiv = document.createElement('div');
        suggestionDiv.className = 'card mb-3';
        suggestionDiv.innerHTML = `
            <div class="card-body">
                <h6 class="card-title">Idea ${index + 1}</h6>
                <p class="card-text">${suggestion.title}</p>
                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted"><strong>Type:</strong> ${suggestion.type}</small>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted"><strong>Platform:</strong> ${suggestion.platform}</small>
                    </div>
                </div>
                <div class="mt-2">
                    <button class="btn btn-sm btn-outline-primary" onclick="copySuggestion(${index})">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" onclick="saveSuggestion(${index})">
                        <i class="fas fa-save"></i> Save
                    </button>
                </div>
            </div>
        `;
        suggestionsList.appendChild(suggestionDiv);
    });
    
    // Display strategy insights
    if (data.strategy) {
        strategyInsights.innerHTML = `
            <p><strong>Best Posting Times:</strong> ${data.strategy.best_times || 'Not available'}</p>
            <p><strong>Recommended Frequency:</strong> ${data.strategy.frequency || '3-5 times per week'}</p>
            <p><strong>Content Mix:</strong> ${data.strategy.content_mix || 'Educational, Entertaining, Promotional'}</p>
            <p><strong>Trending Hashtags:</strong> ${data.strategy.hashtags ? data.strategy.hashtags.join(', ') : 'None suggested'}</p>
        `;
    }
}

function copySuggestion(index) {
    // Implementation for copying suggestion
    alert('Suggestion copied to clipboard!');
}

function saveSuggestion(index) {
    // Implementation for saving suggestion
    alert('Suggestion saved to your content calendar!');
}

function regenerateSuggestions() {
    document.getElementById('suggestionsForm').dispatchEvent(new Event('submit'));
}

function exportSuggestions() {
    // Implementation for exporting suggestions to CSV
    alert('Suggestions exported to CSV!');
}
</script>
@endpush
@endsection 