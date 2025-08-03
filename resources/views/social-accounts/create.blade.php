@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Connect Social Media Account</h5>
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

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0">
                                        <i class="fas fa-link"></i> Connect via OAuth
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small">Connect your accounts securely using OAuth authentication.</p>
                                    
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('oauth.redirect', 'facebook') }}" class="btn btn-outline-primary">
                                            <i class="fab fa-facebook"></i> Connect Facebook
                                        </a>
                                        <a href="{{ route('oauth.redirect', 'twitter') }}" class="btn btn-outline-info">
                                            <i class="fab fa-twitter"></i> Connect Twitter
                                        </a>
                                        <a href="{{ route('oauth.redirect', 'linkedin') }}" class="btn btn-outline-primary">
                                            <i class="fab fa-linkedin"></i> Connect LinkedIn
                                        </a>
                                        <a href="{{ route('oauth.redirect', 'instagram') }}" class="btn btn-outline-danger">
                                            <i class="fab fa-instagram"></i> Connect Instagram
                                        </a>
                                        <a href="{{ route('oauth.redirect', 'youtube') }}" class="btn btn-outline-danger">
                                            <i class="fab fa-youtube"></i> Connect YouTube
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-secondary">
                                <div class="card-header bg-secondary text-white">
                                    <h6 class="mb-0">
                                        <i class="fas fa-key"></i> Manual Connection
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small">Add account details manually (for advanced users).</p>
                                    
                                    <form action="{{ route('social-accounts.store') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="tenant_id" value="{{ app('currentTenant')->id }}">
                                        
                                        <div class="mb-3">
                                            <label for="platform" class="form-label">Platform</label>
                                            <select name="platform" id="platform" class="form-select" required>
                                                <option value="">Select Platform</option>
                                                @foreach($platforms as $key => $name)
                                                    <option value="{{ $key }}" {{ old('platform') == $key ? 'selected' : '' }}>
                                                        {{ $name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label for="username" class="form-label">Username</label>
                                            <input type="text" name="username" id="username" class="form-control" 
                                                   value="{{ old('username') }}" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="account_id" class="form-label">Account ID</label>
                                            <input type="text" name="account_id" id="account_id" class="form-control" 
                                                   value="{{ old('account_id') }}" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="access_token" class="form-label">Access Token</label>
                                            <input type="password" name="access_token" id="access_token" class="form-control" 
                                                   value="{{ old('access_token') }}" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="refresh_token" class="form-label">Refresh Token (Optional)</label>
                                            <input type="password" name="refresh_token" id="refresh_token" class="form-control" 
                                                   value="{{ old('refresh_token') }}">
                                        </div>

                                        <div class="mb-3">
                                            <label for="token_expires_at" class="form-label">Token Expires At (Optional)</label>
                                            <input type="datetime-local" name="token_expires_at" id="token_expires_at" 
                                                   class="form-control" value="{{ old('token_expires_at') }}">
                                        </div>

                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-secondary">
                                                <i class="fas fa-save"></i> Add Account
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <a href="{{ route('social-accounts.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Accounts
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 