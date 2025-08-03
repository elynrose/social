@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-heart"></i> Sentiment Analysis</h4>
                    <p class="text-muted mb-0">Analyze the emotional tone and sentiment of your content</p>
                </div>
                <div class="card-body">
                    <form action="{{ route('ai.sentiment') }}" method="POST" id="sentimentForm">
                        @csrf
                        <div class="mb-3">
                            <label for="content" class="form-label">Content to Analyze *</label>
                            <textarea class="form-control" id="content" name="content" rows="6" placeholder="Enter the content you want to analyze for sentiment..." required></textarea>
                            <div class="form-text">This can be a post, comment, review, or any text content</div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="content_type" class="form-label">Content Type</label>
                                    <select class="form-select" id="content_type" name="content_type">
                                        <option value="post">Social Media Post</option>
                                        <option value="comment">Comment/Review</option>
                                        <option value="caption">Image Caption</option>
                                        <option value="bio">Bio/Description</option>
                                        <option value="ad">Advertisement</option>
                                        <option value="general">General Text</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="language" class="form-label">Language</label>
                                    <select class="form-select" id="language" name="language">
                                        <option value="en">English</option>
                                        <option value="es">Spanish</option>
                                        <option value="fr">French</option>
                                        <option value="de">German</option>
                                        <option value="auto">Auto-detect</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Analysis Options</label>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="emotion_detection" name="options[]" value="emotion" checked>
                                        <label class="form-check-label" for="emotion_detection">Emotion Detection</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="keyword_extraction" name="options[]" value="keywords" checked>
                                        <label class="form-check-label" for="keyword_extraction">Keyword Extraction</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="tone_analysis" name="options[]" value="tone" checked>
                                        <label class="form-check-label" for="tone_analysis">Tone Analysis</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-magic"></i> Analyze Sentiment
                        </button>
                    </form>
                    
                    <div id="sentimentResult" class="mt-4" style="display: none;">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h5><i class="fas fa-chart-pie"></i> Sentiment Analysis Results</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-4">
                                            <h6><i class="fas fa-smile"></i> Overall Sentiment</h6>
                                            <div class="d-flex align-items-center">
                                                <div class="sentiment-score me-3" id="sentimentScore"></div>
                                                <div>
                                                    <h5 id="sentimentLabel"></h5>
                                                    <p class="text-muted mb-0" id="sentimentConfidence"></p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <h6><i class="fas fa-tags"></i> Keywords</h6>
                                            <div id="keywordsList" class="d-flex flex-wrap gap-1"></div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-4">
                                            <h6><i class="fas fa-heart"></i> Emotions Detected</h6>
                                            <div id="emotionsList"></div>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <h6><i class="fas fa-volume-up"></i> Tone Analysis</h6>
                                            <div id="toneAnalysis"></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-12">
                                        <div class="mb-4">
                                            <h6><i class="fas fa-lightbulb"></i> Recommendations</h6>
                                            <div id="recommendations" class="alert alert-info"></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline-primary" onclick="exportAnalysis()">
                                        <i class="fas fa-download"></i> Export Report
                                    </button>
                                    <button class="btn btn-outline-success" onclick="regenerateAnalysis()">
                                        <i class="fas fa-redo"></i> Re-analyze
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
    const form = document.getElementById('sentimentForm');
    const resultDiv = document.getElementById('sentimentResult');
    
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
                displaySentimentAnalysis(data);
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

function displaySentimentAnalysis(data) {
    // Display overall sentiment
    const sentimentScore = document.getElementById('sentimentScore');
    const sentimentLabel = document.getElementById('sentimentLabel');
    const sentimentConfidence = document.getElementById('sentimentConfidence');
    
    sentimentScore.innerHTML = getSentimentIcon(data.sentiment);
    sentimentLabel.textContent = data.sentiment.charAt(0).toUpperCase() + data.sentiment.slice(1);
    sentimentConfidence.textContent = `Confidence: ${data.confidence}%`;
    
    // Display keywords
    const keywordsList = document.getElementById('keywordsList');
    keywordsList.innerHTML = '';
    if (data.keywords && data.keywords.length > 0) {
        data.keywords.forEach(keyword => {
            const badge = document.createElement('span');
            badge.className = 'badge bg-secondary';
            badge.textContent = keyword;
            keywordsList.appendChild(badge);
        });
    }
    
    // Display emotions
    const emotionsList = document.getElementById('emotionsList');
    emotionsList.innerHTML = '';
    if (data.emotions && data.emotions.length > 0) {
        data.emotions.forEach(emotion => {
            const emotionDiv = document.createElement('div');
            emotionDiv.className = 'd-flex justify-content-between align-items-center mb-2';
            emotionDiv.innerHTML = `
                <span>${emotion.name}</span>
                <div class="progress flex-grow-1 mx-2" style="height: 8px;">
                    <div class="progress-bar" style="width: ${emotion.score}%"></div>
                </div>
                <small>${emotion.score}%</small>
            `;
            emotionsList.appendChild(emotionDiv);
        });
    }
    
    // Display tone analysis
    const toneAnalysis = document.getElementById('toneAnalysis');
    toneAnalysis.innerHTML = '';
    if (data.tone) {
        Object.entries(data.tone).forEach(([tone, score]) => {
            const toneDiv = document.createElement('div');
            toneDiv.className = 'd-flex justify-content-between align-items-center mb-2';
            toneDiv.innerHTML = `
                <span>${tone.charAt(0).toUpperCase() + tone.slice(1)}</span>
                <div class="progress flex-grow-1 mx-2" style="height: 8px;">
                    <div class="progress-bar bg-info" style="width: ${score}%"></div>
                </div>
                <small>${score}%</small>
            `;
            toneAnalysis.appendChild(toneDiv);
        });
    }
    
    // Display recommendations
    const recommendations = document.getElementById('recommendations');
    recommendations.innerHTML = '';
    if (data.recommendations && data.recommendations.length > 0) {
        const ul = document.createElement('ul');
        ul.className = 'mb-0';
        data.recommendations.forEach(rec => {
            const li = document.createElement('li');
            li.textContent = rec;
            ul.appendChild(li);
        });
        recommendations.appendChild(ul);
    }
}

function getSentimentIcon(sentiment) {
    const icons = {
        'positive': '<i class="fas fa-smile text-success" style="font-size: 2rem;"></i>',
        'negative': '<i class="fas fa-frown text-danger" style="font-size: 2rem;"></i>',
        'neutral': '<i class="fas fa-meh text-warning" style="font-size: 2rem;"></i>',
        'mixed': '<i class="fas fa-question-circle text-info" style="font-size: 2rem;"></i>'
    };
    return icons[sentiment] || icons['neutral'];
}

function exportAnalysis() {
    // Implementation for exporting analysis report
    alert('Analysis report exported!');
}

function regenerateAnalysis() {
    document.getElementById('sentimentForm').dispatchEvent(new Event('submit'));
}
</script>
@endpush
@endsection 