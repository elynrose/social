@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h4 class="mb-0">Edit Tenant: {{ $tenant->name }}</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('tenants.update', $tenant) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Tenant Name *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $tenant->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="slug" class="form-label">Tenant Slug *</label>
                            <input type="text" class="form-control @error('slug') is-invalid @enderror" 
                                   id="slug" name="slug" value="{{ old('slug', $tenant->slug) }}" required>
                            <div class="form-text">This will be used for subdomain access (e.g., yourslug.localhost:8001)</div>
                            @error('slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="plan_id" class="form-label">Subscription Plan</label>
                            <select class="form-select @error('plan_id') is-invalid @enderror" 
                                    id="plan_id" name="plan_id">
                                <option value="">Select a plan (optional)</option>
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}" 
                                            {{ old('plan_id', $tenant->plan_id) == $plan->id ? 'selected' : '' }}>
                                        {{ $plan->name }} - ${{ $plan->price }}/month
                                    </option>
                                @endforeach
                            </select>
                            @error('plan_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('tenants.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Tenant</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Tenant Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Owner:</strong> {{ $tenant->owner->name }}
                    </div>
                    <div class="mb-3">
                        <strong>Created:</strong> {{ $tenant->created_at->format('M d, Y') }}
                    </div>
                    <div class="mb-3">
                        <strong>Subscription Status:</strong>
                        @if($tenant->hasActiveSubscription())
                            <span class="badge bg-success">Active</span>
                        @elseif($tenant->onTrial())
                            <span class="badge bg-warning text-dark">Trial</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                    </div>
                    @if($tenant->trial_ends_at)
                        <div class="mb-3">
                            <strong>Trial Ends:</strong> {{ $tenant->trial_ends_at->format('M d, Y') }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Team Members</h5>
                </div>
                <div class="card-body">
                    @if($tenantUsers->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($tenantUsers as $user)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $user->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $user->email }}</small>
                                    </div>
                                    <span class="badge bg-info">{{ ucfirst($user->pivot->role) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">No team members found.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 