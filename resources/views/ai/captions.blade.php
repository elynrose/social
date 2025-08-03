@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-quote-left"></i> Caption Generator</h4>
                    <p class="text-muted mb-0">Generate engaging captions for your social media posts</p>
                </div>
                <div class="card-body">
                    <form action="{{ route('ai.captions') }}" method="POST" id="captionForm">
                        @csrf
                        <div class="mb-3">
                            <label for="image_description" class="form-label">Image Description *</label>
                            <textarea class="form-control" id="image_description" name="image_description" rows="4" placeholder="Describe your image or post content in detail..." required></textarea>
                            <div class="form-text">Be specific about what's in the image, colors, mood, etc.</div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tone" class="form-label">Tone</label>
                                    <select class="form-select" id="tone" name="tone">
                                        <option value="professional">Professional</option>
                                        <option value="casual">Casual</option>
                                        <option value="funny">Funny</option>
                                        <option value="inspirational">Inspirational</option>
                                        <option value="educational">Educational</option>
                                        <option value="promotional">Promotional</option>
                                        <option value="personal">Personal</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="platform" class="form-label">Platform</label>
                                    <select class="form-select" id="platform" name="platform">
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
                            <label for="hashtags" class="form-label">Include Hashtags</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="hashtags" name="hashtags" value="1">
                                <label class="form-check-label" for="hashtags">
                                    Generate relevant hashtags
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="call_to_action" class="form-label">Call to Action</label>
                            <select class="form-select" id="call_to_action" name="call_to_action">
                                <option value="">None</option>
                                <option value="like">Ask for likes</option>
                                <option value="comment">Ask for comments</option>
                                <option value="share">Ask for shares</option>
                                <option value="visit">Ask to visit website</option>
                                <option value="follow">Ask to follow</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-magic"></i> Generate Caption
                        </button>
                    </form>
                    
                    <div id="captionResult" class="mt-4" style="display: none;">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5><i class="fas fa-check-circle"></i> Generated Caption</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Caption:</label>
                                    <div class="border rounded p-3 bg-light" id="generatedCaption"></div>
                                </div>
                                <div class="mb-3" id="hashtagsSection" style="display: none;">
                                    <label class="form-label">Suggested Hashtags:</label>
                                    <div class="border rounded p-3 bg-light" id="generatedHashtags"></div>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline-primary" onclick="copyToClipboard('generatedCaption')">
                                        <i class="fas fa-copy"></i> Copy Caption
                                    </button>
                                    <button class="btn btn-outline-secondary" onclick="copyToClipboard('generatedHashtags')" id="copyHashtagsBtn" style="display: none;">
                                        <i class="fas fa-copy"></i> Copy Hashtags
                                    </button>
                                    <button class="btn btn-outline-success" onclick="regenerateCaption()">
                                        <i class="fas fa-redo"></i> Regenerate
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
    const form = document.getElementById('captionForm');
    const resultDiv = document.getElementById('captionResult');
    
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
                displayCaption(data);
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

function displayCaption(data) {
    document.getElementById('generatedCaption').textContent = data.caption;
    
    if (data.hashtags && data.hashtags.length > 0) {
        document.getElementById('generatedHashtags').textContent = data.hashtags.join(' ');
        document.getElementById('hashtagsSection').style.display = 'block';
        document.getElementById('copyHashtagsBtn').style.display = 'inline-block';
    } else {
        document.getElementById('hashtagsSection').style.display = 'none';
        document.getElementById('copyHashtagsBtn').style.display = 'none';
    }
}

function copyToClipboard(elementId) {
    const text = document.getElementById(elementId).textContent;
    navigator.clipboard.writeText(text).then(() => {
        // Show success message
        const btn = event.target.closest('button');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
        btn.classList.add('btn-success');
        btn.classList.remove('btn-outline-primary', 'btn-outline-secondary');
        
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-primary');
        }, 2000);
    });
}

function regenerateCaption() {
    document.getElementById('captionForm').dispatchEvent(new Event('submit'));
}
</script>
@endpush
@endsection 