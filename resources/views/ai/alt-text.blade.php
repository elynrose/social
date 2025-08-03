@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-image"></i> Alt Text Generator</h4>
                    <p class="text-muted mb-0">Generate accessible alt text descriptions for your images</p>
                </div>
                <div class="card-body">
                    <form action="{{ route('ai.alt-text') }}" method="POST" id="altTextForm">
                        @csrf
                        <div class="mb-3">
                            <label for="image_url" class="form-label">Image URL *</label>
                            <input type="url" class="form-control" id="image_url" name="image_url" placeholder="https://example.com/image.jpg" required>
                            <div class="form-text">Provide a direct link to the image you want to describe</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="context" class="form-label">Context (Optional)</label>
                            <textarea class="form-control" id="context" name="context" rows="3" placeholder="Additional context about the image, such as its purpose, audience, or surrounding content..."></textarea>
                            <div class="form-text">This helps generate more relevant and accurate descriptions</div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="detail_level" class="form-label">Detail Level</label>
                                    <select class="form-select" id="detail_level" name="detail_level">
                                        <option value="basic">Basic</option>
                                        <option value="detailed">Detailed</option>
                                        <option value="comprehensive">Comprehensive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="purpose" class="form-label">Image Purpose</label>
                                    <select class="form-select" id="purpose" name="purpose">
                                        <option value="decorative">Decorative</option>
                                        <option value="informative">Informative</option>
                                        <option value="functional">Functional</option>
                                        <option value="branding">Branding</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-magic"></i> Generate Alt Text
                        </button>
                    </form>
                    
                    <div id="altTextResult" class="mt-4" style="display: none;">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5><i class="fas fa-check-circle"></i> Generated Alt Text</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Alt Text:</label>
                                    <div class="border rounded p-3 bg-light" id="generatedAltText"></div>
                                </div>
                                <div class="mb-3" id="accessibilityInfo" style="display: none;">
                                    <label class="form-label">Accessibility Information:</label>
                                    <div class="border rounded p-3 bg-info bg-opacity-10" id="accessibilityDetails"></div>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline-primary" onclick="copyToClipboard('generatedAltText')">
                                        <i class="fas fa-copy"></i> Copy Alt Text
                                    </button>
                                    <button class="btn btn-outline-success" onclick="regenerateAltText()">
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
    const form = document.getElementById('altTextForm');
    const resultDiv = document.getElementById('altTextResult');
    
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
                displayAltText(data);
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

function displayAltText(data) {
    document.getElementById('generatedAltText').textContent = data.alt_text;
    
    if (data.accessibility_info) {
        document.getElementById('accessibilityDetails').innerHTML = `
            <p><strong>WCAG Compliance:</strong> ${data.accessibility_info.wcag_level || 'Level AA'}</p>
            <p><strong>Character Count:</strong> ${data.alt_text.length} characters</p>
            <p><strong>Recommendation:</strong> ${data.accessibility_info.recommendation || 'Good length for screen readers'}</p>
        `;
        document.getElementById('accessibilityInfo').style.display = 'block';
    } else {
        document.getElementById('accessibilityInfo').style.display = 'none';
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
        btn.classList.remove('btn-outline-primary');
        
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-primary');
        }, 2000);
    });
}

function regenerateAltText() {
    document.getElementById('altTextForm').dispatchEvent(new Event('submit'));
}
</script>
@endpush
@endsection 