@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h4><i class="fas fa-exclamation-triangle"></i> Data Deletion Request</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle"></i> Important Notice</h6>
                        <p class="mb-0">You are about to request the deletion of your personal data. This action cannot be undone and will permanently remove your data from our systems.</p>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6><i class="fas fa-user"></i> Data to be Deleted</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success"></i> Personal profile information</li>
                                <li><i class="fas fa-check text-success"></i> Social media connections</li>
                                <li><i class="fas fa-check text-success"></i> Post history and content</li>
                                <li><i class="fas fa-check text-success"></i> Analytics and engagement data</li>
                                <li><i class="fas fa-check text-success"></i> Comments and interactions</li>
                                <li><i class="fas fa-check text-success"></i> Billing and payment information</li>
                                <li><i class="fas fa-check text-success"></i> API configurations and tokens</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-shield-alt"></i> Data Retention</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-info text-info"></i> Legal compliance records</li>
                                <li><i class="fas fa-info text-info"></i> Financial transaction logs</li>
                                <li><i class="fas fa-info text-info"></i> Security audit trails</li>
                                <li><i class="fas fa-info text-info"></i> Backup data (30 days)</li>
                            </ul>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">
                            <h6><i class="fas fa-clock"></i> Deletion Timeline</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 text-center">
                                    <div class="border rounded p-3">
                                        <i class="fas fa-calendar-day text-primary fa-2x mb-2"></i>
                                        <h6>Immediate</h6>
                                        <small class="text-muted">Account deactivation</small>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="border rounded p-3">
                                        <i class="fas fa-calendar-week text-warning fa-2x mb-2"></i>
                                        <h6>7 Days</h6>
                                        <small class="text-muted">Personal data removal</small>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="border rounded p-3">
                                        <i class="fas fa-calendar-alt text-info fa-2x mb-2"></i>
                                        <h6>30 Days</h6>
                                        <small class="text-muted">Backup cleanup</small>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="border rounded p-3">
                                        <i class="fas fa-calendar-check text-success fa-2x mb-2"></i>
                                        <h6>90 Days</h6>
                                        <small class="text-muted">Complete deletion</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('compliance.delete') }}" method="POST" id="deleteForm">
                        @csrf
                        
                        <div class="mb-4">
                            <h6><i class="fas fa-list-check"></i> Confirmation Checklist</h6>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="confirm_understanding" required>
                                <label class="form-check-label" for="confirm_understanding">
                                    I understand that this action is permanent and cannot be undone
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="confirm_backup" required>
                                <label class="form-check-label" for="confirm_backup">
                                    I have backed up any important data I want to keep
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="confirm_legal" required>
                                <label class="form-check-label" for="confirm_legal">
                                    I understand that some data may be retained for legal compliance
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="confirm_services" required>
                                <label class="form-check-label" for="confirm_services">
                                    I understand that this will cancel all active services and subscriptions
                                </label>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="deletion_reason" class="form-label">Reason for Deletion (Optional)</label>
                            <select class="form-select" id="deletion_reason" name="deletion_reason">
                                <option value="">Select a reason...</option>
                                <option value="privacy_concerns">Privacy concerns</option>
                                <option value="no_longer_needed">No longer needed</option>
                                <option value="switching_service">Switching to another service</option>
                                <option value="unsatisfactory_experience">Unsatisfactory experience</option>
                                <option value="gdpr_request">GDPR right to erasure</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="feedback" class="form-label">Additional Feedback (Optional)</label>
                            <textarea class="form-control" id="feedback" name="feedback" rows="3" placeholder="Please share any feedback to help us improve our service..."></textarea>
                        </div>

                        <div class="mb-4">
                            <label for="email_confirmation" class="form-label">Email Confirmation</label>
                            <input type="email" class="form-control" id="email_confirmation" name="email_confirmation" value="{{ auth()->user()->email }}" readonly>
                            <div class="form-text">A confirmation email will be sent to this address</div>
                        </div>

                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> What Happens Next?</h6>
                            <ol class="mb-0">
                                <li>Your account will be immediately deactivated</li>
                                <li>You'll receive a confirmation email</li>
                                <li>Personal data will be removed within 7 days</li>
                                <li>Backup data will be cleaned up within 30 days</li>
                                <li>Complete deletion will occur within 90 days</li>
                            </ol>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-danger" id="deleteBtn" disabled>
                                <i class="fas fa-trash"></i> Confirm Deletion
                            </button>
                            <a href="{{ route('compliance.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Cancel
                            </a>
                            <button type="button" class="btn btn-outline-info" onclick="exportData()">
                                <i class="fas fa-download"></i> Export My Data First
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
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    const deleteBtn = document.getElementById('deleteBtn');
    const deleteForm = document.getElementById('deleteForm');
    
    // Enable/disable delete button based on checkbox status
    function updateDeleteButton() {
        const allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);
        deleteBtn.disabled = !allChecked;
    }
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateDeleteButton);
    });
    
    // Form submission confirmation
    deleteForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const reason = document.getElementById('deletion_reason').value;
        const feedback = document.getElementById('feedback').value;
        
        let confirmMessage = 'Are you absolutely sure you want to delete your account and all associated data? This action cannot be undone.';
        
        if (reason === 'gdpr_request') {
            confirmMessage += '\n\nThis deletion is being processed under GDPR Article 17 (Right to Erasure).';
        }
        
        if (confirm(confirmMessage)) {
            // Show final warning
            if (confirm('FINAL WARNING: This will permanently delete your account and all data. Click OK to proceed with deletion.')) {
                this.submit();
            }
        }
    });
});

function exportData() {
    // Implementation for exporting user data
    alert('Data export initiated. You will receive an email with your data within 24 hours.');
}
</script>
@endpush
@endsection 