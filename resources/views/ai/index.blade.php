@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-robot"></i> AI Content Tools
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 border-primary">
                                <div class="card-body text-center">
                                    <i class="fas fa-quote-left fa-3x text-primary mb-3"></i>
                                    <h6 class="card-title">Generate Captions</h6>
                                    <p class="card-text small text-muted">
                                        Create engaging captions for your social media posts using AI.
                                    </p>
                                    <a href="{{ route('ai.create') }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-magic"></i> Generate Captions
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 border-success">
                                <div class="card-body text-center">
                                    <i class="fas fa-image fa-3x text-success mb-3"></i>
                                    <h6 class="card-title">Alt Text Generator</h6>
                                    <p class="card-text small text-muted">
                                        Generate descriptive alt text for images to improve accessibility.
                                    </p>
                                    <button type="button" class="btn btn-success btn-sm" onclick="showAltTextForm()">
                                        <i class="fas fa-eye"></i> Generate Alt Text
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 border-info">
                                <div class="card-body text-center">
                                    <i class="fas fa-lightbulb fa-3x text-info mb-3"></i>
                                    <h6 class="card-title">Content Suggestions</h6>
                                    <p class="card-text small text-muted">
                                        Get AI-powered content ideas based on your brand voice and topics.
                                    </p>
                                    <button type="button" class="btn btn-info btn-sm" onclick="showSuggestionsForm()">
                                        <i class="fas fa-lightbulb"></i> Get Suggestions
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 border-warning">
                                <div class="card-body text-center">
                                    <i class="fas fa-chart-line fa-3x text-warning mb-3"></i>
                                    <h6 class="card-title">Sentiment Analysis</h6>
                                    <p class="card-text small text-muted">
                                        Analyze the sentiment of your content before posting.
                                    </p>
                                    <button type="button" class="btn btn-warning btn-sm" onclick="showSentimentForm()">
                                        <i class="fas fa-chart-line"></i> Analyze Sentiment
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 border-danger">
                                <div class="card-body text-center">
                                    <i class="fas fa-clock fa-3x text-danger mb-3"></i>
                                    <h6 class="card-title">Post Timing</h6>
                                    <p class="card-text small text-muted">
                                        Optimize when to post your content for maximum engagement.
                                    </p>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="showTimingForm()">
                                        <i class="fas fa-clock"></i> Optimize Timing
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 border-secondary">
                                <div class="card-body text-center">
                                    <i class="fas fa-cogs fa-3x text-secondary mb-3"></i>
                                    <h6 class="card-title">AI Settings</h6>
                                    <p class="card-text small text-muted">
                                        Configure AI preferences and model settings.
                                    </p>
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="showSettings()">
                                        <i class="fas fa-cogs"></i> Configure AI
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

<!-- Alt Text Modal -->
<div class="modal fade" id="altTextModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generate Alt Text</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('ai.alt-text') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="media_path" class="form-label">Media Path</label>
                        <input type="text" name="media_path" id="media_path" class="form-control" required>
                        <div class="form-text">Path to the image file</div>
                    </div>
                    <div class="mb-3">
                        <label for="context" class="form-label">Context (Optional)</label>
                        <textarea name="context" id="context" class="form-control" rows="3"></textarea>
                        <div class="form-text">Additional context about the image</div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-eye"></i> Generate Alt Text
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Suggestions Modal -->
<div class="modal fade" id="suggestionsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Content Suggestions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('ai.suggestions') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="brand_voice" class="form-label">Brand Voice</label>
                        <textarea name="brand_voice" id="brand_voice" class="form-control" rows="3" required></textarea>
                        <div class="form-text">Describe your brand's voice and personality</div>
                    </div>
                    <div class="mb-3">
                        <label for="platform" class="form-label">Platform</label>
                        <select name="platform" id="platform" class="form-select">
                            <option value="">All Platforms</option>
                            <option value="facebook">Facebook</option>
                            <option value="twitter">Twitter</option>
                            <option value="linkedin">LinkedIn</option>
                            <option value="instagram">Instagram</option>
                            <option value="youtube">YouTube</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Topics (Optional)</label>
                        <div id="topics-container">
                            <div class="input-group mb-2">
                                <input type="text" name="topics[]" class="form-control" placeholder="Enter a topic">
                                <button type="button" class="btn btn-outline-secondary" onclick="removeTopic(this)">Remove</button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addTopic()">
                            <i class="fas fa-plus"></i> Add Topic
                        </button>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-lightbulb"></i> Get Suggestions
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Sentiment Modal -->
<div class="modal fade" id="sentimentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sentiment Analysis</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('ai.sentiment') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="text" class="form-label">Text to Analyze</label>
                        <textarea name="text" id="text" class="form-control" rows="5" required></textarea>
                        <div class="form-text">Enter the text you want to analyze for sentiment</div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-chart-line"></i> Analyze Sentiment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Timing Modal -->
<div class="modal fade" id="timingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Optimize Post Timing</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('ai.timing') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="content" class="form-label">Content</label>
                        <textarea name="content" id="content" class="form-control" rows="5" required></textarea>
                        <div class="form-text">Enter the content you want to optimize timing for</div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-clock"></i> Optimize Timing
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showAltTextForm() {
    const modal = new bootstrap.Modal(document.getElementById('altTextModal'));
    modal.show();
}

function showSuggestionsForm() {
    const modal = new bootstrap.Modal(document.getElementById('suggestionsModal'));
    modal.show();
}

function showSentimentForm() {
    const modal = new bootstrap.Modal(document.getElementById('sentimentModal'));
    modal.show();
}

function showTimingForm() {
    const modal = new bootstrap.Modal(document.getElementById('timingModal'));
    modal.show();
}

function showSettings() {
    alert('AI Settings feature coming soon!');
}

function addTopic() {
    const container = document.getElementById('topics-container');
    const newTopic = document.createElement('div');
    newTopic.className = 'input-group mb-2';
    newTopic.innerHTML = `
        <input type="text" name="topics[]" class="form-control" placeholder="Enter a topic">
        <button type="button" class="btn btn-outline-secondary" onclick="removeTopic(this)">Remove</button>
    `;
    container.appendChild(newTopic);
}

function removeTopic(button) {
    button.parentElement.remove();
}
</script>
@endpush
@endsection 