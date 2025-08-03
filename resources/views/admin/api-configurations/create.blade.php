@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header bg-white">
                    <h4 class="mb-0">Add API Configuration</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.api-configurations.store') }}" method="POST" x-data="apiConfig()">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <!-- Platform Selection -->
                                <div class="mb-4">
                                    <label for="platform" class="form-label">Platform</label>
                                    <select class="form-select" id="platform" name="platform" required @change="updateScopes()">
                                        <option value="">Select Platform</option>
                                        @foreach($platformOptions as $platform => $name)
                                            <option value="{{ $platform }}" {{ $selectedPlatform === $platform ? 'selected' : '' }}>
                                                {{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('platform')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Client ID -->
                                <div class="mb-4">
                                    <label for="client_id" class="form-label">Client ID</label>
                                    <input type="text" class="form-control" id="client_id" name="client_id" 
                                           value="{{ old('client_id') }}" required>
                                    @error('client_id')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Your application's client ID from the platform developer console.</div>
                                </div>

                                <!-- Client Secret -->
                                <div class="mb-4">
                                    <label for="client_secret" class="form-label">Client Secret</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="client_secret" name="client_secret" 
                                               value="{{ old('client_secret') }}" required>
                                        <button type="button" class="btn btn-outline-secondary" onclick="togglePassword()">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    @error('client_secret')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Your application's client secret from the platform developer console.</div>
                                </div>

                                <!-- Redirect URI -->
                                <div class="mb-4">
                                    <label for="redirect_uri" class="form-label">Redirect URI</label>
                                    <input type="url" class="form-control" id="redirect_uri" name="redirect_uri" 
                                           value="{{ old('redirect_uri') }}" placeholder="https://yourdomain.com/oauth/{platform}/callback">
                                    @error('redirect_uri')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">The callback URL registered with your application.</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <!-- Scopes -->
                                <div class="mb-4">
                                    <label class="form-label">Scopes</label>
                                    <div id="scopes-container" class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                                        <div class="text-center text-muted">
                                            <i class="fas fa-info-circle"></i>
                                            <p>Select a platform to see available scopes</p>
                                        </div>
                                    </div>
                                    @error('scopes')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Select the permissions your application needs.</div>
                                </div>

                                <!-- Status -->
                                <div class="mb-4">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" 
                                               value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Active
                                        </label>
                                    </div>
                                    <div class="form-text">Enable this configuration for use.</div>
                                </div>

                                <!-- Platform-specific Settings -->
                                <div class="mb-4">
                                    <label class="form-label">Platform Settings</label>
                                    <div id="platform-settings" class="border rounded p-3">
                                        <div class="text-center text-muted">
                                            <i class="fas fa-cog"></i>
                                            <p>Platform-specific settings will appear here</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Help Section -->
                        <div class="mt-4">
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle"></i> Setup Instructions</h6>
                                <div id="setup-instructions">
                                    <p>Select a platform to see specific setup instructions.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.api-configurations.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Configurations
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Configuration
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
function apiConfig() {
    return {
        selectedPlatform: '{{ $selectedPlatform }}',
        
        updateScopes() {
            const platform = document.getElementById('platform').value;
            if (!platform) return;
            
            fetch(`/admin/api-configurations/scopes?platform=${platform}`)
                .then(response => response.json())
                .then(data => {
                    this.displayScopes(data.scopes);
                    this.updateSetupInstructions(platform);
                });
        },
        
        displayScopes(scopes) {
            const container = document.getElementById('scopes-container');
            if (scopes.length === 0) {
                container.innerHTML = '<div class="text-center text-muted"><p>No scopes available</p></div>';
                return;
            }
            
            let html = '';
            scopes.forEach(scope => {
                html += `
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="scopes[]" 
                               value="${scope}" id="scope_${scope.replace(/[^a-zA-Z0-9]/g, '_')}" checked>
                        <label class="form-check-label" for="scope_${scope.replace(/[^a-zA-Z0-9]/g, '_')}">
                            <code>${scope}</code>
                        </label>
                    </div>
                `;
            });
            container.innerHTML = html;
        },
        
        updateSetupInstructions(platform) {
            const instructions = {
                'facebook': `
                    <ol>
                        <li>Go to <a href="https://developers.facebook.com" target="_blank">Facebook Developers</a></li>
                        <li>Create a new app or select an existing one</li>
                        <li>Add Facebook Login product to your app</li>
                        <li>Configure OAuth redirect URIs</li>
                        <li>Copy the App ID and App Secret</li>
                    </ol>
                `,
                'twitter': `
                    <ol>
                        <li>Go to <a href="https://developer.twitter.com" target="_blank">Twitter Developer Portal</a></li>
                        <li>Create a new app or select an existing one</li>
                        <li>Enable OAuth 2.0</li>
                        <li>Configure callback URLs</li>
                        <li>Copy the API Key and API Secret</li>
                    </ol>
                `,
                'linkedin': `
                    <ol>
                        <li>Go to <a href="https://www.linkedin.com/developers" target="_blank">LinkedIn Developers</a></li>
                        <li>Create a new app</li>
                        <li>Configure OAuth 2.0 settings</li>
                        <li>Add redirect URLs</li>
                        <li>Copy the Client ID and Client Secret</li>
                    </ol>
                `,
                'instagram': `
                    <ol>
                        <li>Go to <a href="https://developers.facebook.com" target="_blank">Facebook Developers</a></li>
                        <li>Create a new app or select an existing one</li>
                        <li>Add Instagram Basic Display or Instagram Graph API</li>
                        <li>Configure OAuth redirect URIs</li>
                        <li>Copy the App ID and App Secret</li>
                    </ol>
                `,
                'youtube': `
                    <ol>
                        <li>Go to <a href="https://console.developers.google.com" target="_blank">Google Cloud Console</a></li>
                        <li>Create a new project or select an existing one</li>
                        <li>Enable YouTube Data API v3</li>
                        <li>Create OAuth 2.0 credentials</li>
                        <li>Configure redirect URIs</li>
                        <li>Copy the Client ID and Client Secret</li>
                    </ol>
                `,
                'tiktok': `
                    <ol>
                        <li>Go to <a href="https://developers.tiktok.com" target="_blank">TikTok for Developers</a></li>
                        <li>Create a new app</li>
                        <li>Configure OAuth settings</li>
                        <li>Add redirect URLs</li>
                        <li>Copy the Client Key and Client Secret</li>
                    </ol>
                `
            };
            
            document.getElementById('setup-instructions').innerHTML = instructions[platform] || '<p>No instructions available for this platform.</p>';
        }
    }
}

function togglePassword() {
    const input = document.getElementById('client_secret');
    const button = event.target.closest('button');
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

// Initialize if platform is pre-selected
document.addEventListener('DOMContentLoaded', function() {
    const platform = document.getElementById('platform').value;
    if (platform) {
        const apiConfigInstance = apiConfig();
        apiConfigInstance.updateScopes();
    }
});
</script>
@endpush
@endsection 