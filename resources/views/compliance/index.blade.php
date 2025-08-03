@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-shield-alt"></i> Data Privacy & Compliance
                    </h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <!-- Compliance Overview -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-file-alt fa-2x mb-2"></i>
                                    <h4>{{ $complianceStats['total_posts'] }}</h4>
                                    <p class="mb-0">Posts</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-comments fa-2x mb-2"></i>
                                    <h4>{{ $complianceStats['total_comments'] }}</h4>
                                    <p class="mb-0">Comments</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                                    <h4>{{ $complianceStats['total_approvals'] }}</h4>
                                    <p class="mb-0">Approvals</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-at fa-2x mb-2"></i>
                                    <h4>{{ $complianceStats['total_mentions'] }}</h4>
                                    <p class="mb-0">Mentions</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Compliance Tools -->
                    <div class="row">
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 border-primary">
                                <div class="card-body text-center">
                                    <i class="fas fa-download fa-3x text-primary mb-3"></i>
                                    <h6 class="card-title">Export My Data</h6>
                                    <p class="card-text small text-muted">
                                        Download all your personal data in JSON format for GDPR/CCPA compliance.
                                    </p>
                                    <a href="{{ route('compliance.export') }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-download"></i> Export Data
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 border-danger">
                                <div class="card-body text-center">
                                    <i class="fas fa-trash fa-3x text-danger mb-3"></i>
                                    <h6 class="card-title">Delete My Data</h6>
                                    <p class="card-text small text-muted">
                                        Permanently delete all your personal data. This action cannot be undone.
                                    </p>
                                    <a href="{{ route('compliance.delete-confirm') }}" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i> Delete Data
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 border-info">
                                <div class="card-body text-center">
                                    <i class="fas fa-clock fa-3x text-info mb-3"></i>
                                    <h6 class="card-title">Data Retention</h6>
                                    <p class="card-text small text-muted">
                                        View our data retention policies and how long we keep your information.
                                    </p>
                                    <a href="{{ route('compliance.retention') }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-clock"></i> View Policies
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 border-success">
                                <div class="card-body text-center">
                                    <i class="fas fa-cogs fa-3x text-success mb-3"></i>
                                    <h6 class="card-title">Privacy Settings</h6>
                                    <p class="card-text small text-muted">
                                        Configure your privacy preferences and data sharing settings.
                                    </p>
                                    <a href="{{ route('compliance.settings') }}" class="btn btn-success btn-sm">
                                        <i class="fas fa-cogs"></i> Settings
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 border-warning">
                                <div class="card-body text-center">
                                    <i class="fas fa-file-contract fa-3x text-warning mb-3"></i>
                                    <h6 class="card-title">Privacy Policy</h6>
                                    <p class="card-text small text-muted">
                                        Read our comprehensive privacy policy and data handling practices.
                                    </p>
                                    <button type="button" class="btn btn-warning btn-sm" onclick="showPrivacyPolicy()">
                                        <i class="fas fa-file-contract"></i> View Policy
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 border-secondary">
                                <div class="card-body text-center">
                                    <i class="fas fa-question-circle fa-3x text-secondary mb-3"></i>
                                    <h6 class="card-title">Help & Support</h6>
                                    <p class="card-text small text-muted">
                                        Get help with privacy questions and data protection concerns.
                                    </p>
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="showHelp()">
                                        <i class="fas fa-question-circle"></i> Get Help
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Compliance Information -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Your Rights Under GDPR/CCPA</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6>Right to Access</h6>
                                            <p class="small text-muted">
                                                You have the right to request a copy of all personal data we hold about you.
                                            </p>
                                            <a href="{{ route('compliance.export') }}" class="btn btn-outline-primary btn-sm">
                                                Export My Data
                                            </a>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Right to Deletion</h6>
                                            <p class="small text-muted">
                                                You have the right to request deletion of all your personal data.
                                            </p>
                                            <a href="{{ route('compliance.delete-confirm') }}" class="btn btn-outline-danger btn-sm">
                                                Delete My Data
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showPrivacyPolicy() {
    alert('Privacy Policy feature coming soon!');
}

function showHelp() {
    alert('Help & Support feature coming soon!');
}
</script>
@endpush
@endsection 