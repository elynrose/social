@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-magic"></i> Generate Content with AI</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Caption Generator -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h5><i class="fas fa-quote-left"></i> Caption Generator</h5>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('ai.captions') }}" method="POST" id="captionForm">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="image_description" class="form-label">Image Description</label>
                                            <textarea class="form-control" id="image_description" name="image_description" rows="3" placeholder="Describe your image or post content..."></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label for="tone" class="form-label">Tone</label>
                                            <select class="form-select" id="tone" name="tone">
                                                <option value="professional">Professional</option>
                                                <option value="casual">Casual</option>
                                                <option value="funny">Funny</option>
                                                <option value="inspirational">Inspirational</option>
                                                <option value="educational">Educational</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="platform" class="form-label">Platform</label>
                                            <select class="form-select" id="platform" name="platform">
                                                <option value="instagram">Instagram</option>
                                                <option value="facebook">Facebook</option>
                                                <option value="twitter">Twitter</option>
                                                <option value="linkedin">LinkedIn</option>
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-magic"></i> Generate Caption
                                        </button>
                                    </form>
                                    <div id="captionResult" class="mt-3" style="display: none;">
                                        <div class="alert alert-success">
                                            <h6>Generated Caption:</h6>
                                            <p id="generatedCaption"></p>
                                            <button class="btn btn-sm btn-outline-primary" onclick="copyToClipboard('generatedCaption')">
                                                <i class="fas fa-copy"></i> Copy
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Alt Text Generator -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h5><i class="fas fa-image"></i> Alt Text Generator</h5>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('ai.alt-text') }}" method="POST" id="altTextForm">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="image_url" class="form-label">Image URL</label>
                                            <input type="url" class="form-control" id="image_url" name="image_url" placeholder="https://example.com/image.jpg">
                                        </div>
                                        <div class="mb-3">
                                            <label for="context" class="form-label">Context (Optional)</label>
                                            <textarea class="form-control" id="context" name="context" rows="2" placeholder="Additional context about the image..."></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-magic"></i> Generate Alt Text
                                        </button>
                                    </form>
                                    <div id="altTextResult" class="mt-3" style="display: none;">
                                        <div class="alert alert-success">
                                            <h6>Generated Alt Text:</h6>
                                            <p id="generatedAltText"></p>
                                            <button class="btn btn-sm btn-outline-primary" onclick="copyToClipboard('generatedAltText')">
                                                <i class="fas fa-copy"></i> Copy
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Content Suggestions -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h5><i class="fas fa-lightbulb"></i> Content Suggestions</h5>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('ai.suggestions') }}" method="POST" id="suggestionsForm">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="topic" class="form-label">Topic/Industry</label>
                                            <input type="text" class="form-control" id="topic" name="topic" placeholder="e.g., Technology, Fashion, Food...">
                                        </div>
                                        <div class="mb-3">
                                            <label for="content_type" class="form-label">Content Type</label>
                                            <select class="form-select" id="content_type" name="content_type">
                                                <option value="post">Social Media Post</option>
                                                <option value="blog">Blog Article</option>
                                                <option value="video">Video Script</option>
                                                <option value="story">Story Content</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="audience" class="form-label">Target Audience</label>
                                            <input type="text" class="form-control" id="audience" name="audience" placeholder="e.g., Young professionals, Parents...">
                                        </div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-magic"></i> Get Suggestions
                                        </button>
                                    </form>
                                    <div id="suggestionsResult" class="mt-3" style="display: none;">
                                        <div class="alert alert-info">
                                            <h6>Content Suggestions:</h6>
                                            <ul id="generatedSuggestions"></ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sentiment Analysis -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h5><i class="fas fa-heart"></i> Sentiment Analysis</h5>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('ai.sentiment') }}" method="POST" id="sentimentForm">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="content" class="form-label">Content to Analyze</label>
                                            <textarea class="form-control" id="content" name="content" rows="4" placeholder="Enter the content you want to analyze..."></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-magic"></i> Analyze Sentiment
                                        </button>
                                    </form>
                                    <div id="sentimentResult" class="mt-3" style="display: none;">
                                        <div class="alert alert-info">
                                            <h6>Sentiment Analysis:</h6>
                                            <div id="sentimentAnalysis"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Post Timing Optimization -->
                        <div class="col-md-12 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fas fa-clock"></i> Post Timing Optimization</h5>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('ai.timing') }}" method="POST" id="timingForm">
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="platform_timing" class="form-label">Platform</label>
                                                    <select class="form-select" id="platform_timing" name="platform">
                                                        <option value="instagram">Instagram</option>
                                                        <option value="facebook">Facebook</option>
                                                        <option value="twitter">Twitter</option>
                                                        <option value="linkedin">LinkedIn</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="content_type_timing" class="form-label">Content Type</label>
                                                    <select class="form-select" id="content_type_timing" name="content_type">
                                                        <option value="image">Image Post</option>
                                                        <option value="video">Video</option>
                                                        <option value="story">Story</option>
                                                        <option value="text">Text Post</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="audience_timing" class="form-label">Target Audience</label>
                                                    <select class="form-select" id="audience_timing" name="audience">
                                                        <option value="general">General</option>
                                                        <option value="business">Business</option>
                                                        <option value="youth">Youth (18-25)</option>
                                                        <option value="professionals">Professionals (25-45)</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-magic"></i> Get Optimal Timing
                                        </button>
                                    </form>
                                    <div id="timingResult" class="mt-3" style="display: none;">
                                        <div class="alert alert-success">
                                            <h6>Optimal Posting Times:</h6>
                                            <div id="optimalTiming"></div>
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
<script>
// Handle form submissions with AJAX
document.addEventListener('DOMContentLoaded', function() {
    const forms = ['captionForm', 'altTextForm', 'suggestionsForm', 'sentimentForm', 'timingForm'];
    
    forms.forEach(formId => {
        const form = document.getElementById(formId);
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(form);
                const resultDiv = document.getElementById(formId.replace('Form', 'Result'));
                
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
                        displayResult(formId, data);
                        resultDiv.style.display = 'block';
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                });
            });
        }
    });
});

function displayResult(formId, data) {
    switch(formId) {
        case 'captionForm':
            document.getElementById('generatedCaption').textContent = data.caption;
            break;
        case 'altTextForm':
            document.getElementById('generatedAltText').textContent = data.alt_text;
            break;
        case 'suggestionsForm':
            const suggestionsList = document.getElementById('generatedSuggestions');
            suggestionsList.innerHTML = '';
            data.suggestions.forEach(suggestion => {
                const li = document.createElement('li');
                li.textContent = suggestion;
                suggestionsList.appendChild(li);
            });
            break;
        case 'sentimentForm':
            const sentimentDiv = document.getElementById('sentimentAnalysis');
            sentimentDiv.innerHTML = `
                <p><strong>Sentiment:</strong> ${data.sentiment}</p>
                <p><strong>Confidence:</strong> ${data.confidence}%</p>
                <p><strong>Keywords:</strong> ${data.keywords.join(', ')}</p>
            `;
            break;
        case 'timingForm':
            const timingDiv = document.getElementById('optimalTiming');
            timingDiv.innerHTML = `
                <p><strong>Best Days:</strong> ${data.best_days.join(', ')}</p>
                <p><strong>Best Times:</strong> ${data.best_times.join(', ')}</p>
                <p><strong>Reasoning:</strong> ${data.reasoning}</p>
            `;
            break;
    }
}

function copyToClipboard(elementId) {
    const text = document.getElementById(elementId).textContent;
    navigator.clipboard.writeText(text).then(() => {
        alert('Copied to clipboard!');
    });
}
</script>
@endpush
@endsection 