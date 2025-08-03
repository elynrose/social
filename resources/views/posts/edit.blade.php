@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header bg-white">
                    <h4 class="mb-0">Edit Post</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('posts.update', $post) }}" method="POST" enctype="multipart/form-data" x-data="postEditor()">
                        @csrf
                        @method('PATCH')
                        
                        <div class="row">
                            <div class="col-md-8">
                                <!-- Content Editor -->
                                <div class="mb-4">
                                    <label for="content" class="form-label">Post Content</label>
                                    <x-rich-text-editor name="content" :value="$post->content" />
                                </div>

                                <!-- Media Upload -->
                                <div class="mb-4">
                                    <label for="media" class="form-label">Media</label>
                                    <div class="input-group">
                                        <input type="file" class="form-control" id="media" name="media" accept="image/*,video/*" @change="handleMediaUpload">
                                        <button class="btn btn-outline-secondary" type="button" @click="clearMedia">Clear</button>
                                    </div>
                                    <div class="form-text">Supported formats: JPG, PNG, GIF, MP4, MOV (max 10MB)</div>
                                    
                                    <!-- Current Media -->
                                    @if($post->media_path)
                                        <div class="mt-3">
                                            <h6>Current Media</h6>
                                            <div class="position-relative d-inline-block">
                                                @if(Str::endsWith($post->media_path, ['.jpg', '.jpeg', '.png', '.gif']))
                                                    <img src="{{ Storage::url($post->media_path) }}" class="img-fluid rounded" style="max-height: 200px;" alt="{{ $post->alt_text }}">
                                                @elseif(Str::endsWith($post->media_path, ['.mp4', '.mov']))
                                                    <video src="{{ Storage::url($post->media_path) }}" class="img-fluid rounded" style="max-height: 200px;" controls></video>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <!-- Media Preview -->
                                    <div x-show="mediaPreview" class="mt-3">
                                        <h6>New Media Preview</h6>
                                        <div class="position-relative d-inline-block">
                                            <img x-show="isImage" :src="mediaPreview" class="img-fluid rounded" style="max-height: 200px;">
                                            <video x-show="!isImage" :src="mediaPreview" class="img-fluid rounded" style="max-height: 200px;" controls></video>
                                            <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0" @click="clearMedia">×</button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Alt Text -->
                                <div class="mb-4">
                                    <label for="alt_text" class="form-label">Alt Text</label>
                                    <textarea class="form-control" id="alt_text" name="alt_text" rows="3" placeholder="Describe the media for accessibility">{{ $post->alt_text }}</textarea>
                                    <div class="form-text">Describe the image or video for screen readers and accessibility.</div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <!-- Post Status -->
                                <div class="mb-4">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="draft" {{ $post->status === 'draft' ? 'selected' : '' }}>Draft</option>
                                        <option value="scheduled" {{ $post->status === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                        <option value="published" {{ $post->status === 'published' ? 'selected' : '' }}>Published</option>
                                    </select>
                                </div>

                                <!-- Campaign -->
                                <div class="mb-4">
                                    <label for="campaign_id" class="form-label">Campaign (Optional)</label>
                                    <select class="form-select" name="campaign_id" id="campaign_id">
                                        <option value="">Select Campaign</option>
                                        @foreach($campaigns as $campaign)
                                            <option value="{{ $campaign->id }}" {{ $post->campaign_id === $campaign->id ? 'selected' : '' }}>
                                                {{ $campaign->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Social Account -->
                                <div class="mb-4">
                                    <label for="social_account_id" class="form-label">Social Account</label>
                                    <select class="form-select" name="social_account_id" id="social_account_id">
                                        <option value="">Select Account</option>
                                        @foreach($socialAccounts as $account)
                                            <option value="{{ $account->id }}" {{ $post->social_account_id === $account->id ? 'selected' : '' }}>
                                                {{ ucfirst($account->platform) }} - {{ $account->username }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Post Information -->
                                <div class="mb-4">
                                    <h6>Post Information</h6>
                                    <ul class="list-unstyled small">
                                        <li><strong>Created:</strong> {{ $post->created_at->format('M j, Y g:i A') }}</li>
                                        <li><strong>Last Updated:</strong> {{ $post->updated_at->format('M j, Y g:i A') }}</li>
                                        <li><strong>Author:</strong> {{ $post->user->name }}</li>
                                        @if($post->external_id)
                                            <li><strong>External ID:</strong> {{ $post->external_id }}</li>
                                        @endif
                                    </ul>
                                </div>

                                <!-- Actions -->
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Update Post
                                    </button>
                                    <a href="{{ route('posts.show', $post) }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-eye"></i> View Post
                                    </a>
                                    <a href="{{ route('posts.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left"></i> Back to Posts
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function postEditor() {
    return {
        mediaPreview: null,
        isImage: false,
        
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
        }
    }
}
</script>
@endsection 