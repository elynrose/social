@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>My Tenants</h1>
        <a href="{{ route('tenants.create') }}" class="btn btn-primary">Create New Tenant</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="row">
        @foreach($tenants as $tenant)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ $tenant->name }}</h5>
                        @if(app('currentTenant') && app('currentTenant')->id === $tenant->id)
                            <span class="badge bg-success">Current</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>Slug:</strong> {{ $tenant->slug }}
                        </div>
                        <div class="mb-3">
                            <strong>Owner:</strong> 
                            @if($tenant->owner)
                                {{ $tenant->owner->name }}
                            @else
                                <span class="text-muted">No owner</span>
                            @endif
                        </div>
                        <div class="mb-3">
                            <strong>Plan:</strong> 
                            @if($tenant->plan)
                                <span class="badge bg-primary">{{ $tenant->plan->name }}</span>
                            @else
                                <span class="text-muted">No plan</span>
                            @endif
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
                        <div class="mb-3">
                            <strong>Your Role:</strong>
                            @php
                                $userTenant = $tenant->users()->where('user_id', auth()->id())->first();
                                $userRole = $userTenant ? $userTenant->pivot->role : 'member';
                            @endphp
                            <span class="badge bg-info">{{ ucfirst($userRole) }}</span>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            @if(app('currentTenant') && app('currentTenant')->id !== $tenant->id)
                                <form action="{{ route('tenant.switch') }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="tenant_id" value="{{ $tenant->id }}">
                                    <button type="submit" class="btn btn-sm btn-outline-primary">Switch to This Tenant</button>
                                </form>
                            @else
                                <span class="text-muted">Current Tenant</span>
                            @endif
                            
                            <div class="btn-group">
                                <a href="{{ route('tenants.edit', $tenant) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                @if($userRole === 'owner')
                                    <form action="{{ route('tenants.destroy', $tenant) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this tenant? This action cannot be undone.')">Delete</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if($tenants->count() === 0)
        <div class="text-center py-5">
            <h5 class="text-muted">No tenants found</h5>
            <p class="text-muted">Create your first tenant to get started.</p>
            <a href="{{ route('tenants.create') }}" class="btn btn-primary">Create Tenant</a>
        </div>
    @endif
</div>
@endsection 