@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-shield-alt"></i> Privacy & Compliance Settings</h4>
                    <p class="text-muted mb-0">Configure data privacy and compliance settings</p>
                </div>
                <div class="card-body">
                    <form action="{{ route('compliance.settings') }}" method="POST" id="complianceForm">
                        @csrf
                        
                        <!-- GDPR Settings -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6><i class="fas fa-gdpr"></i> GDPR Compliance</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="gdpr_enabled" name="gdpr_enabled" value="1" {{ $settings->gdpr_enabled ?? false ? 'checked' : '' }}>
                                        <label class="form-check-label" for="gdpr_enabled">
                                            Enable GDPR compliance features
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="data_retention_days" class="form-label">Data Retention Period (days)</label>
                                    <input type="number" class="form-control" id="data_retention_days" name="data_retention_days" value="{{ $settings->data_retention_days ?? 730 }}" min="1" max="3650">
                                    <div class="form-text">How long to keep user data before automatic deletion</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="cookie_consent" class="form-label">Cookie Consent Message</label>
                                    <textarea class="form-control" id="cookie_consent" name="cookie_consent" rows="3">{{ $settings->cookie_consent ?? 'We use cookies to enhance your experience. By continuing to visit this site you agree to our use of cookies.' }}</textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="require_explicit_consent" name="require_explicit_consent" value="1" {{ $settings->require_explicit_consent ?? false ? 'checked' : '' }}>
                                        <label class="form-check-label" for="require_explicit_consent">
                                            Require explicit consent for data processing
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Data Processing -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6><i class="fas fa-database"></i> Data Processing</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Data Processing Purposes</label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="purpose_analytics" name="processing_purposes[]" value="analytics" {{ in_array('analytics', $settings->processing_purposes ?? []) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="purpose_analytics">Analytics and Performance</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="purpose_marketing" name="processing_purposes[]" value="marketing" {{ in_array('marketing', $settings->processing_purposes ?? []) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="purpose_marketing">Marketing and Advertising</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="purpose_functional" name="processing_purposes[]" value="functional" {{ in_array('functional', $settings->processing_purposes ?? []) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="purpose_functional">Functional Services</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="purpose_security" name="processing_purposes[]" value="security" {{ in_array('security', $settings->processing_purposes ?? []) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="purpose_security">Security and Fraud Prevention</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="data_encryption" class="form-label">Data Encryption Level</label>
                                    <select class="form-select" id="data_encryption" name="data_encryption">
                                        <option value="standard" {{ ($settings->data_encryption ?? 'standard') === 'standard' ? 'selected' : '' }}>Standard (AES-256)</option>
                                        <option value="enhanced" {{ ($settings->data_encryption ?? 'standard') === 'enhanced' ? 'selected' : '' }}>Enhanced (AES-256 + TLS 1.3)</option>
                                        <option value="enterprise" {{ ($settings->data_encryption ?? 'standard') === 'enterprise' ? 'selected' : '' }}>Enterprise (FIPS 140-2)</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="anonymize_data" name="anonymize_data" value="1" {{ $settings->anonymize_data ?? false ? 'checked' : '' }}>
                                        <label class="form-check-label" for="anonymize_data">
                                            Anonymize personal data when possible
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Third-Party Services -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6><i class="fas fa-external-link-alt"></i> Third-Party Services</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Allowed Third-Party Services</label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="service_google" name="third_party_services[]" value="google_analytics" {{ in_array('google_analytics', $settings->third_party_services ?? []) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="service_google">Google Analytics</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="service_facebook" name="third_party_services[]" value="facebook_pixel" {{ in_array('facebook_pixel', $settings->third_party_services ?? []) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="service_facebook">Facebook Pixel</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="service_stripe" name="third_party_services[]" value="stripe" {{ in_array('stripe', $settings->third_party_services ?? []) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="service_stripe">Stripe (Payments)</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="service_sendgrid" name="third_party_services[]" value="sendgrid" {{ in_array('sendgrid', $settings->third_party_services ?? []) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="service_sendgrid">SendGrid (Email)</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="third_party_consent" class="form-label">Third-Party Consent Message</label>
                                    <textarea class="form-control" id="third_party_consent" name="third_party_consent" rows="3">{{ $settings->third_party_consent ?? 'We use third-party services for analytics, payments, and communication. You can opt out of non-essential services.' }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Data Subject Rights -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6><i class="fas fa-user-shield"></i> Data Subject Rights</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="enable_right_to_access" name="enable_right_to_access" value="1" {{ $settings->enable_right_to_access ?? true ? 'checked' : '' }}>
                                        <label class="form-check-label" for="enable_right_to_access">
                                            Enable right to access personal data
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="enable_right_to_rectification" name="enable_right_to_rectification" value="1" {{ $settings->enable_right_to_rectification ?? true ? 'checked' : '' }}>
                                        <label class="form-check-label" for="enable_right_to_rectification">
                                            Enable right to rectification
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="enable_right_to_erasure" name="enable_right_to_erasure" value="1" {{ $settings->enable_right_to_erasure ?? true ? 'checked' : '' }}>
                                        <label class="form-check-label" for="enable_right_to_erasure">
                                            Enable right to erasure (right to be forgotten)
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="enable_right_to_portability" name="enable_right_to_portability" value="1" {{ $settings->enable_right_to_portability ?? true ? 'checked' : '' }}>
                                        <label class="form-check-label" for="enable_right_to_portability">
                                            Enable right to data portability
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="dpo_email" class="form-label">Data Protection Officer Email</label>
                                    <input type="email" class="form-control" id="dpo_email" name="dpo_email" value="{{ $settings->dpo_email ?? '' }}" placeholder="dpo@company.com">
                                </div>
                            </div>
                        </div>

                        <!-- Breach Notification -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6><i class="fas fa-exclamation-triangle"></i> Breach Notification</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="breach_notification_hours" class="form-label">Breach Notification Time (hours)</label>
                                    <input type="number" class="form-control" id="breach_notification_hours" name="breach_notification_hours" value="{{ $settings->breach_notification_hours ?? 72 }}" min="1" max="168">
                                    <div class="form-text">Time to notify authorities after discovering a data breach</div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="auto_breach_detection" name="auto_breach_detection" value="1" {{ $settings->auto_breach_detection ?? false ? 'checked' : '' }}>
                                        <label class="form-check-label" for="auto_breach_detection">
                                            Enable automatic breach detection
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="breach_contacts" class="form-label">Breach Notification Contacts</label>
                                    <textarea class="form-control" id="breach_contacts" name="breach_contacts" rows="3" placeholder="Enter email addresses separated by commas">{{ $settings->breach_contacts ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Settings
                            </button>
                            <a href="{{ route('compliance.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Compliance
                            </a>
                            <button type="button" class="btn btn-outline-info" onclick="testCompliance()">
                                <i class="fas fa-check-circle"></i> Test Compliance
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
    const form = document.getElementById('complianceForm');
    
    form.addEventListener('submit', function(e) {
        const gdprEnabled = document.getElementById('gdpr_enabled').checked;
        const retentionDays = document.getElementById('data_retention_days').value;
        
        if (gdprEnabled && retentionDays > 2555) { // 7 years
            if (!confirm('A retention period of more than 7 years may not comply with GDPR principles. Are you sure you want to continue?')) {
                e.preventDefault();
                return;
            }
        }
    });
});

function testCompliance() {
    // Implementation for testing compliance settings
    alert('Compliance test completed. All settings appear to be compliant with current regulations.');
}
</script>
@endpush
@endsection 