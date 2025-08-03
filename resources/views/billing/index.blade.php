@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-8">
            <!-- Current Plan -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Current Plan</h5>
                </div>
                <div class="card-body">
                    @if($subscription)
                        <div class="row">
                            <div class="col-md-6">
                                <h6>{{ $subscription->plan->name ?? 'Current Plan' }}</h6>
                                <p class="text-muted mb-2">
                                    ${{ number_format($subscription->plan->price / 100, 2) }}/month
                                </p>
                                <p class="mb-0">
                                    <small class="text-muted">
                                        Status: 
                                        <span class="badge bg-{{ $subscription->status === 'active' ? 'success' : 'warning' }}">
                                            {{ ucfirst($subscription->status) }}
                                        </span>
                                    </small>
                                </p>
                            </div>
                            <div class="col-md-6 text-end">
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="cancelSubscription()">
                                    Cancel Subscription
                                </button>
                            </div>
                        </div>
                    @else
                        <p class="text-muted mb-0">No active subscription</p>
                    @endif
                </div>
            </div>

            <!-- Payment Method -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Payment Method</h5>
                </div>
                <div class="card-body">
                    @if($paymentMethod)
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-credit-card"></i>
                                <span class="ms-2">•••• •••• •••• {{ $paymentMethod->last4 }}</span>
                                <small class="text-muted ms-2">{{ ucfirst($paymentMethod->brand) }}</small>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="updatePaymentMethod()">
                                Update
                            </button>
                        </div>
                    @else
                        <p class="text-muted mb-0">No payment method on file</p>
                    @endif
                </div>
            </div>

            <!-- Billing History -->
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Billing History</h5>
                </div>
                <div class="card-body">
                    <div id="billingHistory">
                        <div class="text-center">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Available Plans -->
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Available Plans</h5>
                </div>
                <div class="card-body">
                    @foreach($plans as $plan)
                        <div class="plan-card mb-3 p-3 border rounded">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="mb-0">{{ $plan->name }}</h6>
                                <span class="badge bg-primary">${{ number_format($plan->price / 100, 2) }}/month</span>
                            </div>
                            <p class="text-muted small mb-3">{{ $plan->description }}</p>
                            
                            <ul class="list-unstyled small mb-3">
                                @foreach($plan->features ?? [] as $feature)
                                    <li><i class="fas fa-check text-success me-2"></i>{{ $feature }}</li>
                                @endforeach
                            </ul>
                            
                            @if(!$subscription || $subscription->plan_id !== $plan->id)
                                <button type="button" class="btn btn-primary btn-sm w-100" onclick="subscribeToPlan({{ $plan->id }})">
                                    Subscribe
                                </button>
                            @else
                                <button type="button" class="btn btn-secondary btn-sm w-100" disabled>
                                    Current Plan
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Method Modal -->
<div class="modal fade" id="paymentMethodModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Payment Method</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="payment-form">
                    <div id="card-element" class="form-control mb-3">
                        <!-- Stripe Elements will insert the card input here -->
                    </div>
                    <div id="card-errors" class="text-danger small mb-3" role="alert"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="savePaymentMethod()">Save</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
const stripe = Stripe('{{ config("services.stripe.key") }}');
let elements;
let card;

document.addEventListener('DOMContentLoaded', function() {
    loadBillingHistory();
});

function loadBillingHistory() {
    fetch('/api/billing/invoices')
        .then(response => response.json())
        .then(data => {
            const historyDiv = document.getElementById('billingHistory');
            
            if (data.invoices && data.invoices.length > 0) {
                let html = '<div class="table-responsive"><table class="table table-sm">';
                html += '<thead><tr><th>Date</th><th>Amount</th><th>Status</th><th></th></tr></thead><tbody>';
                
                data.invoices.forEach(invoice => {
                    const date = new Date(invoice.created * 1000).toLocaleDateString();
                    const amount = (invoice.amount_paid / 100).toFixed(2);
                    const status = invoice.status;
                    
                    html += `<tr>
                        <td>${date}</td>
                        <td>$${amount}</td>
                        <td><span class="badge bg-${status === 'paid' ? 'success' : 'warning'}">${status}</span></td>
                        <td>
                            ${invoice.hosted_invoice_url ? `<a href="${invoice.hosted_invoice_url}" target="_blank" class="btn btn-sm btn-outline-primary">View</a>` : ''}
                        </td>
                    </tr>`;
                });
                
                html += '</tbody></table></div>';
                historyDiv.innerHTML = html;
            } else {
                historyDiv.innerHTML = '<p class="text-muted text-center">No billing history available</p>';
            }
        })
        .catch(error => {
            console.error('Error loading billing history:', error);
            document.getElementById('billingHistory').innerHTML = '<p class="text-danger text-center">Failed to load billing history</p>';
        });
}

function subscribeToPlan(planId) {
    // Create payment method first
    createPaymentMethod().then(paymentMethodId => {
        if (paymentMethodId) {
            fetch('/api/billing/subscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    plan_id: planId,
                    payment_method_id: paymentMethodId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Subscription created successfully!', 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showToast(data.message || 'Failed to create subscription', 'error');
                }
            })
            .catch(error => {
                console.error('Error creating subscription:', error);
                showToast('Failed to create subscription', 'error');
            });
        }
    });
}

function updatePaymentMethod() {
    const modal = new bootstrap.Modal(document.getElementById('paymentMethodModal'));
    modal.show();
    
    // Initialize Stripe Elements
    if (!elements) {
        elements = stripe.elements();
        card = elements.create('card');
        card.mount('#card-element');
        
        card.addEventListener('change', function(event) {
            const displayError = document.getElementById('card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
        });
    }
}

function savePaymentMethod() {
    stripe.createPaymentMethod({
        type: 'card',
        card: card,
    }).then(function(result) {
        if (result.error) {
            document.getElementById('card-errors').textContent = result.error.message;
        } else {
            fetch('/api/billing/update-payment-method', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    payment_method_id: result.paymentMethod.id
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Payment method updated successfully!', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('paymentMethodModal')).hide();
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showToast(data.message || 'Failed to update payment method', 'error');
                }
            })
            .catch(error => {
                console.error('Error updating payment method:', error);
                showToast('Failed to update payment method', 'error');
            });
        }
    });
}

function createPaymentMethod() {
    return new Promise((resolve, reject) => {
        if (!elements) {
            elements = stripe.elements();
            card = elements.create('card');
        }
        
        stripe.createPaymentMethod({
            type: 'card',
            card: card,
        }).then(function(result) {
            if (result.error) {
                showToast(result.error.message, 'error');
                reject(result.error);
            } else {
                resolve(result.paymentMethod.id);
            }
        });
    });
}

function cancelSubscription() {
    if (confirm('Are you sure you want to cancel your subscription? You will continue to have access until the end of your current billing period.')) {
        fetch('/api/billing/cancel-subscription', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Subscription cancelled successfully', 'success');
                setTimeout(() => window.location.reload(), 1500);
            } else {
                showToast(data.message || 'Failed to cancel subscription', 'error');
            }
        })
        .catch(error => {
            console.error('Error cancelling subscription:', error);
            showToast('Failed to cancel subscription', 'error');
        });
    }
}

function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999;';
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}
</script>
@endpush
@endsection