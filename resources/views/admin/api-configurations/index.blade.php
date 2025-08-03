@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">API Configurations</h4>
                    <a href="{{ route('admin.api-configurations.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Configuration
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if($configurations->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Platform</th>
                                        <th>Client ID</th>
                                        <th>Redirect URI</th>
                                        <th>Scopes</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($configurations as $config)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="fab fa-{{ strtolower($config->platform) }} fa-2x me-3"></i>
                                                    <div>
                                                        <strong>{{ ucfirst($config->platform) }}</strong>
                                                        @if($config->isConfigured())
                                                            <span class="badge bg-success ms-2">Configured</span>
                                                        @else
                                                            <span class="badge bg-warning ms-2">Incomplete</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <code class="text-muted">{{ Str::limit($config->client_id, 20) }}</code>
                                            </td>
                                            <td>
                                                @if($config->redirect_uri)
                                                    <small class="text-muted">{{ Str::limit($config->redirect_uri, 30) }}</small>
                                                @else
                                                    <span class="text-muted">Not set</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($config->scopes && count($config->scopes) > 0)
                                                    <span class="badge bg-info">{{ count($config->scopes) }} scopes</span>
                                                    <button type="button" class="btn btn-sm btn-outline-info ms-1" 
                                                            data-bs-toggle="tooltip" 
                                                            title="{{ implode(', ', $config->scopes) }}">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                @else
                                                    <span class="text-muted">No scopes</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($config->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.api-configurations.edit', $config) }}" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    @if($config->isConfigured())
                                                        <button type="button" class="btn btn-sm btn-outline-info"
                                                                onclick="testConfiguration({{ $config->id }})">
                                                            <i class="fas fa-vial"></i>
                                                        </button>
                                                    @endif
                                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                                            onclick="deleteConfiguration({{ $config->id }})">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-cogs fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No API configurations</h5>
                            <p class="text-muted">Add your first API configuration to start connecting social media accounts.</p>
                            <a href="{{ route('admin.api-configurations.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add First Configuration
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Platform Setup Guide -->
            <div class="card mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Platform Setup Guide</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($platformOptions as $platform => $name)
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="border rounded p-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fab fa-{{ strtolower($platform) }} fa-2x me-2"></i>
                                        <h6 class="mb-0">{{ $name }}</h6>
                                    </div>
                                    <small class="text-muted">
                                        @if($configurations->where('platform', $platform)->count() > 0)
                                            <span class="text-success">✓ Configured</span>
                                        @else
                                            <span class="text-warning">Not configured</span>
                                        @endif
                                    </small>
                                    <div class="mt-2">
                                        <a href="{{ route('admin.api-configurations.create', ['platform' => $platform]) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            {{ $configurations->where('platform', $platform)->count() > 0 ? 'Edit' : 'Setup' }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
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

function deleteConfiguration(configId) {
    if (confirm('Are you sure you want to delete this API configuration? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/api-configurations/${configId}`;
        form.innerHTML = `
            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
            <input type="hidden" name="_method" value="DELETE">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush
@endsection 