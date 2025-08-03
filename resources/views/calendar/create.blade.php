@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-plus"></i> Schedule Post
                    </h5>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('calendar.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="post_id" class="form-label">Select Post</label>
                            <select name="post_id" id="post_id" class="form-select" required>
                                <option value="">Choose a post to schedule</option>
                                @foreach($posts as $post)
                                    <option value="{{ $post->id }}" {{ old('post_id') == $post->id ? 'selected' : '' }}>
                                        {{ Str::limit($post->content, 100) }}
                                        @if($post->campaign)
                                            (Campaign: {{ $post->campaign->name }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Select a draft post to schedule for publishing.</div>
                        </div>

                        <div class="mb-3">
                            <label for="publish_at" class="form-label">Publish Date & Time</label>
                            <input type="datetime-local" name="publish_at" id="publish_at" 
                                   class="form-control" value="{{ old('publish_at') }}" required>
                            <div class="form-text">Choose when you want this post to be published.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Platforms</label>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="platforms[]" 
                                               value="facebook" id="platform_facebook" 
                                               {{ in_array('facebook', old('platforms', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="platform_facebook">
                                            <i class="fab fa-facebook text-primary"></i> Facebook
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="platforms[]" 
                                               value="twitter" id="platform_twitter"
                                               {{ in_array('twitter', old('platforms', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="platform_twitter">
                                            <i class="fab fa-twitter text-info"></i> Twitter
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="platforms[]" 
                                               value="linkedin" id="platform_linkedin"
                                               {{ in_array('linkedin', old('platforms', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="platform_linkedin">
                                            <i class="fab fa-linkedin text-primary"></i> LinkedIn
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="platforms[]" 
                                               value="instagram" id="platform_instagram"
                                               {{ in_array('instagram', old('platforms', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="platform_instagram">
                                            <i class="fab fa-instagram text-danger"></i> Instagram
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="platforms[]" 
                                               value="youtube" id="platform_youtube"
                                               {{ in_array('youtube', old('platforms', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="platform_youtube">
                                            <i class="fab fa-youtube text-danger"></i> YouTube
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-text">Select the platforms where you want to publish this post.</div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('calendar.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Calendar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-calendar-plus"></i> Schedule Post
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set minimum datetime to now
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const minDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
    
    document.getElementById('publish_at').min = minDateTime;
    
    // If no value is set, set it to 1 hour from now
    if (!document.getElementById('publish_at').value) {
        const oneHourFromNow = new Date(now.getTime() + 60 * 60 * 1000);
        const futureYear = oneHourFromNow.getFullYear();
        const futureMonth = String(oneHourFromNow.getMonth() + 1).padStart(2, '0');
        const futureDay = String(oneHourFromNow.getDate()).padStart(2, '0');
        const futureHours = String(oneHourFromNow.getHours()).padStart(2, '0');
        const futureMinutes = String(oneHourFromNow.getMinutes()).padStart(2, '0');
        const defaultDateTime = `${futureYear}-${futureMonth}-${futureDay}T${futureHours}:${futureMinutes}`;
        
        document.getElementById('publish_at').value = defaultDateTime;
    }
});
</script>
@endpush
@endsection 