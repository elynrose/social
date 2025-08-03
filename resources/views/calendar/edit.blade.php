@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-edit"></i> Edit Scheduled Post
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

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <div>
                                        <strong>Scheduled Post</strong><br>
                                        <small class="text-muted">{{ Str::limit($scheduledPost->post->content, 100) }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('calendar.update', $scheduledPost) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Post Content</label>
                                    <input type="text" class="form-control" 
                                           value="{{ Str::limit($scheduledPost->post->content, 100) }}" readonly>
                                    <div class="form-text">Post content cannot be changed here.</div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Current Status</label>
                                    <div>
                                        @switch($scheduledPost->status)
                                            @case('scheduled')
                                                <span class="badge bg-primary">Scheduled</span>
                                                @break
                                            @case('published')
                                                <span class="badge bg-success">Published</span>
                                                @break
                                            @case('failed')
                                                <span class="badge bg-danger">Failed</span>
                                                @break
                                            @case('cancelled')
                                                <span class="badge bg-secondary">Cancelled</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary">{{ ucfirst($scheduledPost->status) }}</span>
                                        @endswitch
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="publish_at" class="form-label">Publish Date & Time</label>
                            <input type="datetime-local" name="publish_at" id="publish_at" 
                                   class="form-control" 
                                   value="{{ old('publish_at', $scheduledPost->publish_at->format('Y-m-d\TH:i')) }}" 
                                   required>
                            <div class="form-text">Choose when you want this post to be published.</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Scheduled For</label>
                                    <div class="text-muted">
                                        {{ $scheduledPost->publish_at->format('M j, Y \a\t g:i A') }}
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Created</label>
                                    <div class="text-muted">
                                        {{ $scheduledPost->created_at->format('M j, Y \a\t g:i A') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('calendar.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Calendar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Schedule
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
});
</script>
@endpush
@endsection 