<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tenant;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class WebhookController extends Controller
{
    public function stripe(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (SignatureVerificationException $e) {
            Log::error('Stripe webhook signature verification failed', [
                'error' => $e->getMessage(),
            ]);
            return response('Invalid signature', 400);
        }

        switch ($event->type) {
            case 'customer.subscription.created':
                $this->handleSubscriptionCreated($event->data->object);
                break;
            case 'customer.subscription.updated':
                $this->handleSubscriptionUpdated($event->data->object);
                break;
            case 'customer.subscription.deleted':
                $this->handleSubscriptionDeleted($event->data->object);
                break;
            case 'invoice.payment_succeeded':
                $this->handlePaymentSucceeded($event->data->object);
                break;
            case 'invoice.payment_failed':
                $this->handlePaymentFailed($event->data->object);
                break;
            default:
                Log::info('Unhandled Stripe webhook event', ['type' => $event->type]);
        }

        return response('Webhook handled', 200);
    }

    protected function handleSubscriptionCreated($subscription)
    {
        $tenant = Tenant::where('stripe_id', $subscription->customer)->first();
        
        if ($tenant) {
            $tenant->update([
                'stripe_id' => $subscription->customer,
                'trial_ends_at' => null,
            ]);

            Log::info('Subscription created for tenant', [
                'tenant_id' => $tenant->id,
                'subscription_id' => $subscription->id,
            ]);
        }
    }

    protected function handleSubscriptionUpdated($subscription)
    {
        $tenant = Tenant::where('stripe_id', $subscription->customer)->first();
        
        if ($tenant) {
            $tenant->update([
                'trial_ends_at' => null,
            ]);

            Log::info('Subscription updated for tenant', [
                'tenant_id' => $tenant->id,
                'subscription_id' => $subscription->id,
                'status' => $subscription->status,
            ]);
        }
    }

    protected function handleSubscriptionDeleted($subscription)
    {
        $tenant = Tenant::where('stripe_id', $subscription->customer)->first();
        
        if ($tenant) {
            // Optionally downgrade to free plan or mark as inactive
            Log::info('Subscription deleted for tenant', [
                'tenant_id' => $tenant->id,
                'subscription_id' => $subscription->id,
            ]);
        }
    }

    protected function handlePaymentSucceeded($invoice)
    {
        $tenant = Tenant::where('stripe_id', $invoice->customer)->first();
        
        if ($tenant) {
            Log::info('Payment succeeded for tenant', [
                'tenant_id' => $tenant->id,
                'invoice_id' => $invoice->id,
                'amount' => $invoice->amount_paid,
            ]);
        }
    }

    protected function handlePaymentFailed($invoice)
    {
        $tenant = Tenant::where('stripe_id', $invoice->customer)->first();
        
        if ($tenant) {
            Log::warning('Payment failed for tenant', [
                'tenant_id' => $tenant->id,
                'invoice_id' => $invoice->id,
                'attempt_count' => $invoice->attempt_count,
            ]);
        }
    }
} 