@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Campaigns</h1>
        <a href="{{ route('campaigns.create') }}" class="btn btn-primary">New Campaign</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            @if($campaigns->count() > 0)
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Goal</th>
                            <th>Posts</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($campaigns as $campaign)
                            <tr>
                                <td>{{ $campaign->name }}</td>
                                <td>{{ $campaign->start_date ? $campaign->start_date->format('M d, Y') : '—' }}</td>
                                <td>{{ $campaign->end_date ? $campaign->end_date->format('M d, Y') : '—' }}</td>
                                <td>{{ Str::limit($campaign->goal, 50) ?: '—' }}</td>
                                <td>{{ $campaign->posts_count ?? 0 }}</td>
                                <td>
                                    <a href="{{ route('campaigns.edit', $campaign) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form action="{{ route('campaigns.destroy', $campaign) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this campaign?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="text-center py-5">
                    <h5 class="text-muted">No campaigns found</h5>
                    <p class="text-muted">Create your first campaign to get started.</p>
                    <a href="{{ route('campaigns.create') }}" class="btn btn-primary">Create Campaign</a>
                </div>
            @endif
        </div>
    </div>

    @if($campaigns->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $campaigns->links() }}
        </div>
    @endif
</div>
@endsection 