@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Campaign Details</h4>
                    <div>
                        <a href="{{ route('campaigns.edit', $campaign) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('campaigns.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h5>{{ $campaign->name }}</h5>
                            <p class="text-muted mb-4">{{ $campaign->goal }}</p>
                            
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <strong>Start Date:</strong>
                                    <p>{{ $campaign->start_date ? $campaign->start_date->format('M j, Y') : 'Not set' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <strong>End Date:</strong>
                                    <p>{{ $campaign->end_date ? $campaign->end_date->format('M j, Y') : 'Not set' }}</p>
                                </div>
                            </div>

                            <div class="mb-4">
                                <strong>Status:</strong>
                                <span class="badge bg-{{ $campaign->status === 'active' ? 'success' : ($campaign->status === 'draft' ? 'secondary' : 'warning') }}">
                                    {{ ucfirst($campaign->status) }}
                                </span>
                            </div>

                            @if($campaign->posts->count() > 0)
                            <div class="mb-4">
                                <h6>Campaign Posts</h6>
                                <div class="list-group">
                                    @foreach($campaign->posts as $post)
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ Str::limit($post->content, 100) }}</strong>
                                                <br>
                                                <small class="text-muted">
                                                    {{ $post->created_at->format('M j, Y g:i A') }}
                                                </small>
                                            </div>
                                            <span class="badge bg-{{ $post->status === 'published' ? 'success' : ($post->status === 'draft' ? 'secondary' : 'warning') }}">
                                                {{ ucfirst($post->status) }}
                                            </span>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Campaign Stats</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <h4>{{ $campaign->posts->count() }}</h4>
                                            <small class="text-muted">Posts</small>
                                        </div>
                                        <div class="col-6">
                                            <h4>{{ $campaign->posts->where('status', 'published')->count() }}</h4>
                                            <small class="text-muted">Published</small>
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
@endsection 