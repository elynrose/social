@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header bg-white">
                    <h4 class="mb-0">Create New Post</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data" x-data="postCreator()">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-8">
                                <!-- Content Editor -->
                                <div class="mb-4">
                                    <label for="content" class="form-label">Post Content</label>
                                    <x-rich-text-editor name="content" :value="old('content')" />
                                </div>

                                <!-- Media Upload -->
                                <div class="mb-4">
                                    <label for="media" class="form-label">Media</label>
                                    <div class="input-group">
                                        <input type="file" class="form-control" id="media" name="media" accept="image/*,video/*" @change="handleMediaUpload">
                                        <button class="btn btn-outline-secondary" type="button" @click="clearMedia">Clear</button>
                                    </div>
                                    <div class="form-text">Supported formats: JPG, PNG, GIF, MP4, MOV (max 10MB)</div>
                                    
                                    <!-- Media Preview -->
                                    <div x-show="mediaPreview" class="mt-3">
                                        <div class="position-relative d-inline-block">
                                            <img x-show="isImage" :src="mediaPreview" class="img-fluid rounded" style="max-height: 200px;">
                                            <video x-show="!isImage" :src="mediaPreview" class="img-fluid rounded" style="max-height: 200px;" controls></video>
                                            <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0" @click="clearMedia">×</button>
                                        </div>
                                    </div>
                                </div>

                                <!-- AI Features -->
                                <div class="mb-4">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">AI Assistant</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <button type="button" class="btn btn-outline-primary btn-sm w-100 mb-2" @click="generateCaptions">
                                                        <i class="fas fa-magic"></i> Generate Captions
                                                    </button>
                                                </div>
                                                <div class="col-md-6">
                                                    <button type="button" class="btn btn-outline-primary btn-sm w-100 mb-2" @click="generateAltText">
                                                        <i class="fas fa-eye"></i> Generate Alt Text
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            <!-- Generated Captions -->
                                            <div x-show="captions.length > 0" class="mt-3">
                                                <label class="form-label">Generated Captions</label>
                                                <div class="list-group">
                                                    <template x-for="(caption, index) in captions" :key="index">
                                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                                            <span x-text="caption"></span>
                                                            <button type="button" class="btn btn-sm btn-outline-primary" @click="useCaption(caption)">Use</button>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <!-- Social Account Selection -->
                                <div class="mb-4">
                                    <label for="social_account_id" class="form-label">Social Account</label>
                                    <select class="form-select" name="social_account_id" id="social_account_id" required>
                                        <option value="">Select Social Account</option>
                                        @foreach($socialAccounts ?? [] as $account)
                                            <option value="{{ $account->id }}" data-platform="{{ $account->platform }}">
                                                <i class="fab fa-{{ $account->platform }}"></i> {{ $account->username }} ({{ ucfirst($account->platform) }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-text">Select the social media account to post to</div>
                                </div>

                                <!-- Platform Selection (for future multi-platform posting) -->
                                <div class="mb-4">
                                    <label class="form-label">Additional Platforms</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="platforms[]" value="facebook" id="facebook" disabled>
                                        <label class="form-check-label" for="facebook">
                                            <i class="fab fa-facebook text-primary"></i> Facebook
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="platforms[]" value="twitter" id="twitter" disabled>
                                        <label class="form-check-label" for="twitter">
                                            <i class="fab fa-twitter text-info"></i> Twitter
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="platforms[]" value="linkedin" id="linkedin" disabled>
                                        <label class="form-check-label" for="linkedin">
                                            <i class="fab fa-linkedin text-primary"></i> LinkedIn
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="platforms[]" value="instagram" id="instagram" disabled>
                                        <label class="form-check-label" for="instagram">
                                            <i class="fab fa-instagram text-danger"></i> Instagram
                                        </label>
                                    </div>
                                    <div class="form-text text-muted">Multi-platform posting coming soon</div>
                                </div>

                                <!-- Scheduling -->
                                <div class="mb-4">
                                    <label class="form-label">Publishing</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="publish_type" value="now" id="publish_now" checked>
                                        <label class="form-check-label" for="publish_now">Publish Now</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="publish_type" value="schedule" id="publish_schedule">
                                        <label class="form-check-label" for="publish_schedule">Schedule</label>
                                    </div>
                                    
                                    <div x-show="showSchedule" class="mt-2">
                                        <input type="datetime-local" class="form-control" name="scheduled_at" x-ref="scheduleInput">
                                    </div>
                                </div>

                                <!-- Campaign -->
                                <div class="mb-4">
                                    <label for="campaign_id" class="form-label">Campaign (Optional)</label>
                                    <select class="form-select" name="campaign_id" id="campaign_id">
                                        <option value="">Select Campaign</option>
                                        @foreach($campaigns ?? [] as $campaign)
                                            <option value="{{ $campaign->id }}">{{ $campaign->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Approval Required -->
                                <div class="mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="requires_approval" id="requires_approval">
                                        <label class="form-check-label" for="requires_approval">Requires Approval</label>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane"></i> Create Post
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" @click="saveDraft">
                                        <i class="fas fa-save"></i> Save as Draft
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Post Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="previewContent"></div>
            </div>
        </div>
    </div>
</div>

<script>
function postCreator() {
    return {
        mediaPreview: null,
        isImage: false,
        captions: [],
        showSchedule: false,
        
        init() {
            // Watch for schedule radio button changes
            this.$watch('showSchedule', (value) => {
                if (value) {
                    this.$nextTick(() => {
                        this.$refs.scheduleInput.focus();
                    });
                }
            });
            
            // Check for scheduled date parameter from calendar
            const urlParams = new URLSearchParams(window.location.search);
            const scheduledDate = urlParams.get('scheduled_date');
            if (scheduledDate) {
                // Set the schedule radio button
                document.getElementById('publish_schedule').checked = true;
                this.showSchedule = true;
                
                // Format the date for datetime-local input
                const date = new Date(scheduledDate);
                const formattedDate = date.toISOString().slice(0, 16);
                
                // Set the scheduled date input
                this.$nextTick(() => {
                    this.$refs.scheduleInput.value = formattedDate;
                });
            }
        },
        
        handleMediaUpload(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.mediaPreview = e.target.result;
                    this.isImage = file.type.startsWith('image/');
                };
                reader.readAsDataURL(file);
            }
        },
        
        clearMedia() {
            this.mediaPreview = null;
            this.isImage = false;
            document.getElementById('media').value = '';
        },
        
        async generateCaptions() {
            const content = document.querySelector('[name="content"]').value;
            if (!content.trim()) {
                alert('Please add some content first');
                return;
            }
            
            try {
                const response = await fetch('/api/ai/generate-captions', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ content })
                });
                
                const data = await response.json();
                this.captions = data.captions || [];
            } catch (error) {
                console.error('Failed to generate captions:', error);
                alert('Failed to generate captions. Please try again.');
            }
        },
        
        async generateAltText() {
            if (!this.mediaPreview) {
                alert('Please upload media first');
                return;
            }
            
            try {
                const response = await fetch('/api/ai/generate-alt-text', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ 
                        media_path: this.mediaPreview,
                        context: document.querySelector('[name="content"]').value
                    })
                });
                
                const data = await response.json();
                if (data.alt_text) {
                    // Add alt text to a hidden field or display it
                    console.log('Generated alt text:', data.alt_text);
                }
            } catch (error) {
                console.error('Failed to generate alt text:', error);
                alert('Failed to generate alt text. Please try again.');
            }
        },
        
        useCaption(caption) {
            const editor = document.querySelector('[contenteditable="true"]');
            if (editor) {
                editor.innerHTML = caption;
                // Trigger the update event
                editor.dispatchEvent(new Event('input'));
            }
        },
        
        saveDraft() {
            // Add a hidden field to indicate this is a draft
            const draftField = document.createElement('input');
            draftField.type = 'hidden';
            draftField.name = 'status';
            draftField.value = 'draft';
            document.querySelector('form').appendChild(draftField);
            
            // Submit the form
            document.querySelector('form').submit();
        }
    }
}

// Watch for schedule radio button
document.addEventListener('DOMContentLoaded', function() {
    const scheduleRadio = document.getElementById('publish_schedule');
    const scheduleDiv = document.querySelector('[x-show="showSchedule"]');
    
    scheduleRadio.addEventListener('change', function() {
        if (this.checked) {
            scheduleDiv.style.display = 'block';
        } else {
            scheduleDiv.style.display = 'none';
        }
    });
});
</script>

@push('scripts')
<script src="https://kit.fontawesome.com/your-fontawesome-kit.js"></script>
@endpush
@endsection