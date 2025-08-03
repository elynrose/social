@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Approvals</h1>
    
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <!-- Pending Approvals Section -->
    @if($pendingApprovals->count() > 0)
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="mb-0">Pending Your Approval</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Post Content</th>
                                <th>Author</th>
                                <th>Campaign</th>
                                <th>Step</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingApprovals as $approval)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ Str::limit($approval->post->content, 100) }}</div>
                                        <small class="text-muted">{{ $approval->post->created_at->format('M d, Y H:i') }}</small>
                                    </td>
                                    <td>{{ $approval->post->user->name }}</td>
                                    <td>{{ $approval->post->campaign->name ?? 'No Campaign' }}</td>
                                    <td><span class="badge bg-info">Step {{ $approval->step }}</span></td>
                                    <td>
                                        <form action="{{ route('approval.approve', $approval) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $approval->id }}">Reject</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- All Approvals Section -->
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">All Approvals</h5>
        </div>
        <div class="card-body">
            @if($allApprovals->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Post Content</th>
                                <th>Author</th>
                                <th>Approver</th>
                                <th>Status</th>
                                <th>Step</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($allApprovals as $approval)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ Str::limit($approval->post->content, 100) }}</div>
                                        <small class="text-muted">{{ $approval->post->created_at->format('M d, Y H:i') }}</small>
                                    </td>
                                    <td>{{ $approval->post->user->name }}</td>
                                    <td>{{ $approval->user->name }}</td>
                                    <td>
                                        @if($approval->status === 'pending')
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @elseif($approval->status === 'approved')
                                            <span class="badge bg-success">Approved</span>
                                        @else
                                            <span class="badge bg-danger">Rejected</span>
                                        @endif
                                    </td>
                                    <td><span class="badge bg-info">Step {{ $approval->step }}</span></td>
                                    <td>{{ $approval->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                @if($allApprovals->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $allApprovals->links() }}
                    </div>
                @endif
            @else
                <div class="text-center py-5">
                    <h5 class="text-muted">No approvals found</h5>
                    <p class="text-muted">Approvals will appear here when posts are submitted for review.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Reject Modals -->
@foreach($pendingApprovals as $approval)
    <div class="modal fade" id="rejectModal{{ $approval->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Post</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('approval.reject', $approval) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="comments" class="form-label">Rejection Comments</label>
                            <textarea class="form-control" name="comments" rows="3" placeholder="Please provide feedback on why this post was rejected..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject Post</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
@endsection