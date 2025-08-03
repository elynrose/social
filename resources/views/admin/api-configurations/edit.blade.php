@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header bg-white">
                    <h4 class="mb-0">Edit API Configuration - {{ ucfirst($apiConfiguration->platform) }}</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.api-configurations.update', $apiConfiguration) }}" method="POST" x-data="apiConfig()">
                        @csrf
                        @method('PATCH')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <!-- Platform (Read-only) -->
                                <div class="mb-4">
                                    <label class="form-label">Platform</label>
                                    <input type="text" class="form-control" value="{{ ucfirst($apiConfiguration->platform) }}" readonly>
                                    <div class="form-text">Platform cannot be changed after creation.</div>
                                </div>

                                <!-- Client ID -->
                                <div class="mb-4">
                                    <label for="client_id" class="form-label">Client ID</label>
                                    <input type="text" class="form-control" id="client_id" name="client_id" 
                                           value="{{ old('client_id', $apiConfiguration->client_id) }}" required>
                                    @error('client_id')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Client Secret -->
                                <div class="mb-4">
                                    <label for="client_secret" class="form-label">Client Secret</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="client_secret" name="client_secret" 
                                               value="{{ old('client_secret', $apiConfiguration->client_secret) }}" required>
                                        <button type="button" class="btn btn-outline-secondary" onclick="togglePassword()">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    @error('client_secret')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Redirect URI -->
                                <div class="mb-4">
                                    <label for="redirect_uri" class="form-label">Redirect URI</label>
                                    <input type="url" class="form-control" id="redirect_uri" name="redirect_uri" 
                                           value="{{ old('redirect_uri', $apiConfiguration->redirect_uri) }}">
                                    @error('redirect_uri')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <!-- Scopes -->
                                <div class="mb-4">
                                    <label class="form-label">Scopes</label>
                                    <div id="scopes-container" class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                                        @if($apiConfiguration->scopes && count($apiConfiguration->scopes) > 0)
                                            @foreach($apiConfiguration->scopes as $scope)
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" name="scopes[]" 
                                                           value="{{ $scope }}" id="scope_{{ Str::slug($scope) }}" checked>
                                                    <label class="form-check-label" for="scope_{{ Str::slug($scope) }}">
                                                        <code>{{ $scope }}</code>
                                                    </label>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="text-center text-muted">
                                                <p>No scopes configured</p>
                                            </div>
                                        @endif
                                    </div>
                                    @error('scopes')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Status -->
                                <div class="mb-4">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" 
                                               value="1" {{ old('is_active', $apiConfiguration->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Active
                                        </label>
                                    </div>
                                </div>

                                <!-- Configuration Status -->
                                <div class="mb-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6>Configuration Status</h6>
                                            <div class="d-flex align-items-center mb-2">
                                                @if($apiConfiguration->isConfigured())
                                                    <i class="fas fa-check-circle text-success me-2"></i>
                                                    <span class="text-success">Fully Configured</span>
                                                @else
                                                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                                    <span class="text-warning">Incomplete Configuration</span>
                                                @endif
                                            </div>
                                            <small class="text-muted">
                                                @if($apiConfiguration->client_id && $apiConfiguration->client_secret)
                                                    Ready for OAuth connections
                                                @else
                                                    Missing required credentials
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Test Configuration -->
                        @if($apiConfiguration->isConfigured())
                            <div class="mt-4">
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-vial"></i> Test Configuration</h6>
                                    <p>Verify that your API credentials are working correctly.</p>
                                    <button type="button" class="btn btn-outline-info btn-sm" 
                                            onclick="testConfiguration({{ $apiConfiguration->id }})">
                                        <i class="fas fa-vial"></i> Test Configuration
                                    </button>
                                </div>
                            </div>
                        @endif

                        <!-- Actions -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.api-configurations.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Configurations
                            </a>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Configuration
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Test Configuration Modal -->
<div class="modal fade" id="testModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Test API Configuration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="test-result">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Testing...</span>
                        </div>
                        <p class="mt-2">Testing API configuration...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function apiConfig() {
    return {
        // Configuration methods if needed
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

function testConfiguration(configId) {
    const modal = new bootstrap.Modal(document.getElementById('testModal'));
    modal.show();
    
    fetch(`/admin/api-configurations/${configId}/test`)
        .then(response => response.json())
        .then(data => {
            const resultDiv = document.getElementById('test-result');
            if (data.success) {
                resultDiv.innerHTML = `
                    <div class="text-center text-success">
                        <i class="fas fa-check-circle fa-3x mb-3"></i>
                        <h5>Configuration Valid</h5>
                        <p>${data.message}</p>
                    </div>
                `;
            } else {
                resultDiv.innerHTML = `
                    <div class="text-center text-danger">
                        <i class="fas fa-times-circle fa-3x mb-3"></i>
                        <h5>Configuration Invalid</h5>
                        <p>${data.message}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            const resultDiv = document.getElementById('test-result');
            resultDiv.innerHTML = `
                <div class="text-center text-danger">
                    <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                    <h5>Test Failed</h5>
                    <p>An error occurred while testing the configuration.</p>
                </div>
            `;
        });
}
</script>
@endpush
@endsection 