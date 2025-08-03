@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Edit Social Media Account</h5>
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
                                    @switch($socialAccount->platform)
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
                                    <div>
                                        <strong>{{ ucfirst($socialAccount->platform) }} Account</strong><br>
                                        <small class="text-muted">Account ID: {{ $socialAccount->account_id }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('social-accounts.update', $socialAccount) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" name="username" id="username" class="form-control" 
                                           value="{{ old('username', $socialAccount->username) }}" required>
                                    <div class="form-text">The display name for this account.</div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Platform</label>
                                    <input type="text" class="form-control" value="{{ ucfirst($socialAccount->platform) }}" readonly>
                                    <div class="form-text">Platform cannot be changed after creation.</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="access_token" class="form-label">Access Token</label>
                                    <input type="password" name="access_token" id="access_token" class="form-control" 
                                           value="{{ old('access_token') }}" required>
                                    <div class="form-text">The OAuth access token for this account.</div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="refresh_token" class="form-label">Refresh Token (Optional)</label>
                                    <input type="password" name="refresh_token" id="refresh_token" class="form-control" 
                                           value="{{ old('refresh_token') }}">
                                    <div class="form-text">Used to refresh the access token when it expires.</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="token_expires_at" class="form-label">Token Expires At (Optional)</label>
                            <input type="datetime-local" name="token_expires_at" id="token_expires_at" 
                                   class="form-control" 
                                   value="{{ old('token_expires_at', $socialAccount->token_expires_at ? $socialAccount->token_expires_at->format('Y-m-d\TH:i') : '') }}">
                            <div class="form-text">When the access token will expire (if applicable).</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Connection Status</label>
                                    <div>
                                        @if($socialAccount->token_expires_at && $socialAccount->token_expires_at->isPast())
                                            <span class="badge bg-danger">Token Expired</span>
                                        @elseif($socialAccount->token_expires_at)
                                            <span class="badge bg-success">Token Active</span>
                                        @else
                                            <span class="badge bg-warning">Status Unknown</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Connected Since</label>
                                    <div class="text-muted">
                                        {{ $socialAccount->created_at->format('M j, Y') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('social-accounts.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Accounts
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 