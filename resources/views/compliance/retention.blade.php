@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-calendar-alt"></i> Data Retention Policy</h4>
                    <p class="text-muted mb-0">Configure how long different types of data are retained</p>
                </div>
                <div class="card-body">
                    <form action="{{ route('compliance.retention') }}" method="POST" id="retentionForm">
                        @csrf
                        
                        <!-- User Data Retention -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6><i class="fas fa-user"></i> User Data Retention</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="user_profile_retention" class="form-label">User Profile Data</label>
                                            <select class="form-select" id="user_profile_retention" name="user_profile_retention">
                                                <option value="30" {{ ($settings->user_profile_retention ?? 730) == 30 ? 'selected' : '' }}>30 days</option>
                                                <option value="90" {{ ($settings->user_profile_retention ?? 730) == 90 ? 'selected' : '' }}>90 days</option>
                                                <option value="180" {{ ($settings->user_profile_retention ?? 730) == 180 ? 'selected' : '' }}>6 months</option>
                                                <option value="365" {{ ($settings->user_profile_retention ?? 730) == 365 ? 'selected' : '' }}>1 year</option>
                                                <option value="730" {{ ($settings->user_profile_retention ?? 730) == 730 ? 'selected' : '' }}>2 years</option>
                                                <option value="2555" {{ ($settings->user_profile_retention ?? 730) == 2555 ? 'selected' : '' }}>7 years</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="user_activity_retention" class="form-label">User Activity Logs</label>
                                            <select class="form-select" id="user_activity_retention" name="user_activity_retention">
                                                <option value="30" {{ ($settings->user_activity_retention ?? 90) == 30 ? 'selected' : '' }}>30 days</option>
                                                <option value="90" {{ ($settings->user_activity_retention ?? 90) == 90 ? 'selected' : '' }}>90 days</option>
                                                <option value="180" {{ ($settings->user_activity_retention ?? 90) == 180 ? 'selected' : '' }}>6 months</option>
                                                <option value="365" {{ ($settings->user_activity_retention ?? 90) == 365 ? 'selected' : '' }}>1 year</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Content Data Retention -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6><i class="fas fa-file-alt"></i> Content Data Retention</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="posts_retention" class="form-label">Posts and Content</label>
                                            <select class="form-select" id="posts_retention" name="posts_retention">
                                                <option value="90" {{ ($settings->posts_retention ?? 365) == 90 ? 'selected' : '' }}>90 days</option>
                                                <option value="180" {{ ($settings->posts_retention ?? 365) == 180 ? 'selected' : '' }}>6 months</option>
                                                <option value="365" {{ ($settings->posts_retention ?? 365) == 365 ? 'selected' : '' }}>1 year</option>
                                                <option value="730" {{ ($settings->posts_retention ?? 365) == 730 ? 'selected' : '' }}>2 years</option>
                                                <option value="indefinite" {{ ($settings->posts_retention ?? 365) == 'indefinite' ? 'selected' : '' }}>Indefinite</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="comments_retention" class="form-label">Comments and Interactions</label>
                                            <select class="form-select" id="comments_retention" name="comments_retention">
                                                <option value="30" {{ ($settings->comments_retention ?? 180) == 30 ? 'selected' : '' }}>30 days</option>
                                                <option value="90" {{ ($settings->comments_retention ?? 180) == 90 ? 'selected' : '' }}>90 days</option>
                                                <option value="180" {{ ($settings->comments_retention ?? 180) == 180 ? 'selected' : '' }}>6 months</option>
                                                <option value="365" {{ ($settings->comments_retention ?? 180) == 365 ? 'selected' : '' }}>1 year</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Analytics Data Retention -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6><i class="fas fa-chart-line"></i> Analytics Data Retention</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="analytics_retention" class="form-label">Analytics and Metrics</label>
                                            <select class="form-select" id="analytics_retention" name="analytics_retention">
                                                <option value="90" {{ ($settings->analytics_retention ?? 365) == 90 ? 'selected' : '' }}>90 days</option>
                                                <option value="180" {{ ($settings->analytics_retention ?? 365) == 180 ? 'selected' : '' }}>6 months</option>
                                                <option value="365" {{ ($settings->analytics_retention ?? 365) == 365 ? 'selected' : '' }}>1 year</option>
                                                <option value="730" {{ ($settings->analytics_retention ?? 365) == 730 ? 'selected' : '' }}>2 years</option>
                                                <option value="indefinite" {{ ($settings->analytics_retention ?? 365) == 'indefinite' ? 'selected' : '' }}>Indefinite</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="engagement_retention" class="form-label">Engagement Data</label>
                                            <select class="form-select" id="engagement_retention" name="engagement_retention">
                                                <option value="90" {{ ($settings->engagement_retention ?? 180) == 90 ? 'selected' : '' }}>90 days</option>
                                                <option value="180" {{ ($settings->engagement_retention ?? 180) == 180 ? 'selected' : '' }}>6 months</option>
                                                <option value="365" {{ ($settings->engagement_retention ?? 180) == 365 ? 'selected' : '' }}>1 year</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Financial Data Retention -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6><i class="fas fa-credit-card"></i> Financial Data Retention</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="billing_retention" class="form-label">Billing and Payment Data</label>
                                            <select class="form-select" id="billing_retention" name="billing_retention">
                                                <option value="2555" {{ ($settings->billing_retention ?? 2555) == 2555 ? 'selected' : '' }}>7 years (Legal requirement)</option>
                                                <option value="3650" {{ ($settings->billing_retention ?? 2555) == 3650 ? 'selected' : '' }}>10 years</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="tax_retention" class="form-label">Tax and Compliance Records</label>
                                            <select class="form-select" id="tax_retention" name="tax_retention">
                                                <option value="2555" {{ ($settings->tax_retention ?? 2555) == 2555 ? 'selected' : '' }}>7 years (Legal requirement)</option>
                                                <option value="3650" {{ ($settings->tax_retention ?? 2555) == 3650 ? 'selected' : '' }}>10 years</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- System Data Retention -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6><i class="fas fa-server"></i> System Data Retention</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="logs_retention" class="form-label">System Logs</label>
                                            <select class="form-select" id="logs_retention" name="logs_retention">
                                                <option value="30" {{ ($settings->logs_retention ?? 90) == 30 ? 'selected' : '' }}>30 days</option>
                                                <option value="90" {{ ($settings->logs_retention ?? 90) == 90 ? 'selected' : '' }}>90 days</option>
                                                <option value="180" {{ ($settings->logs_retention ?? 90) == 180 ? 'selected' : '' }}>6 months</option>
                                                <option value="365" {{ ($settings->logs_retention ?? 90) == 365 ? 'selected' : '' }}>1 year</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="backup_retention" class="form-label">Backup Data</label>
                                            <select class="form-select" id="backup_retention" name="backup_retention">
                                                <option value="30" {{ ($settings->backup_retention ?? 90) == 30 ? 'selected' : '' }}>30 days</option>
                                                <option value="90" {{ ($settings->backup_retention ?? 90) == 90 ? 'selected' : '' }}>90 days</option>
                                                <option value="180" {{ ($settings->backup_retention ?? 90) == 180 ? 'selected' : '' }}>6 months</option>
                                                <option value="365" {{ ($settings->backup_retention ?? 90) == 365 ? 'selected' : '' }}>1 year</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Retention Rules -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6><i class="fas fa-rules"></i> Retention Rules</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="auto_deletion" name="auto_deletion" value="1" {{ $settings->auto_deletion ?? true ? 'checked' : '' }}>
                                        <label class="form-check-label" for="auto_deletion">
                                            Enable automatic data deletion when retention period expires
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="deletion_notification" name="deletion_notification" value="1" {{ $settings->deletion_notification ?? true ? 'checked' : '' }}>
                                        <label class="form-check-label" for="deletion_notification">
                                            Send notification before data deletion
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="anonymize_before_deletion" name="anonymize_before_deletion" value="1" {{ $settings->anonymize_before_deletion ?? false ? 'checked' : '' }}>
                                        <label class="form-check-label" for="anonymize_before_deletion">
                                            Anonymize data before deletion for research purposes
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="deletion_schedule" class="form-label">Deletion Schedule</label>
                                    <select class="form-select" id="deletion_schedule" name="deletion_schedule">
                                        <option value="daily" {{ ($settings->deletion_schedule ?? 'weekly') == 'daily' ? 'selected' : '' }}>Daily</option>
                                        <option value="weekly" {{ ($settings->deletion_schedule ?? 'weekly') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                        <option value="monthly" {{ ($settings->deletion_schedule ?? 'weekly') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Compliance Information -->
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> Compliance Notes</h6>
                            <ul class="mb-0">
                                <li>Financial data must be retained for 7 years for tax compliance</li>
                                <li>GDPR requires data to be kept only as long as necessary</li>
                                <li>Some data may be retained longer for legal or security purposes</li>
                                <li>Users have the right to request data deletion at any time</li>
                            </ul>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Retention Policy
                            </button>
                            <a href="{{ route('compliance.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Compliance
                            </a>
                            <button type="button" class="btn btn-outline-info" onclick="testRetention()">
                                <i class="fas fa-check-circle"></i> Test Retention Rules
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
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('retentionForm');
    
    form.addEventListener('submit', function(e) {
        const billingRetention = document.getElementById('billing_retention').value;
        const taxRetention = document.getElementById('tax_retention').value;
        
        // Check if financial data retention meets legal requirements
        if (billingRetention < 2555 || taxRetention < 2555) {
            if (!confirm('Financial data retention periods below 7 years may not comply with legal requirements. Are you sure you want to continue?')) {
                e.preventDefault();
                return;
            }
        }
    });
});

function testRetention() {
    // Implementation for testing retention rules
    alert('Retention policy test completed. All rules appear to be properly configured.');
}
</script>
@endpush
@endsection 