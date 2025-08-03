@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Social Media Accounts</h5>
                    <a href="{{ route('social-accounts.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Connect Account
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($socialAccounts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Platform</th>
                                        <th>Username</th>
                                        <th>Account ID</th>
                                        <th>Status</th>
                                        <th>Connected</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($socialAccounts as $account)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @switch($account->platform)
                                                        @case('facebook')
                                                            <i class="fab fa-facebook text-primary me-2"></i>
                                                            @break
                                                        @case('twitter')
                                                            <i class="fab fa-twitter text-info me-2"></i>
                                                            @break
                                                        @case('linkedin')
                                                            <i class="fab fa-linkedin text-primary me-2"></i>
                                                            @break
                                                        @case('instagram')
                                                            <i class="fab fa-instagram text-danger me-2"></i>
                                                            @break
                                                        @case('youtube')
                                                            <i class="fab fa-youtube text-danger me-2"></i>
                                                            @break
                                                        @case('tiktok')
                                                            <i class="fab fa-tiktok text-dark me-2"></i>
                                                            @break
                                                        @default
                                                            <i class="fas fa-globe text-secondary me-2"></i>
                                                    @endswitch
                                                    <span class="text-capitalize">{{ $account->platform }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <strong>{{ $account->username }}</strong>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $account->account_id }}</small>
                                            </td>
                                            <td>
                                                @if($account->token_expires_at && $account->token_expires_at->isPast())
                                                    <span class="badge bg-danger">Expired</span>
                                                @elseif($account->token_expires_at)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-warning">Unknown</span>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ $account->created_at->diffForHumans() }}
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('social-accounts.edit', $account) }}" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('social-accounts.destroy', $account) }}" 
                                                          method="POST" 
                                                          class="d-inline"
                                                          onsubmit="return confirm('Are you sure you want to disconnect this account?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-share-alt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Social Accounts Connected</h5>
                            <p class="text-muted">Connect your social media accounts to start posting content.</p>
                            <a href="{{ route('social-accounts.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Connect Your First Account
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 