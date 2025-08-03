<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Subscription;

class BillingController extends Controller
{
    public function index()
    {
        $tenant = app('currentTenant');
        $plans = Plan::all();
        
        // Get current subscription info
        $subscription = null;
        $paymentMethod = null;
        
        if ($tenant->stripe_id) {
            try {
                Stripe::setApiKey(config('services.stripe.secret'));
                
                $customer = Customer::retrieve($tenant->stripe_id);
                $subscription = $customer->subscriptions->data[0] ?? null;
                
                if ($customer->default_source) {
                    $paymentMethod = $customer->default_source;
                }
            } catch (\Exception $e) {
                Log::error('Failed to retrieve Stripe customer', [
                    'tenant_id' => $tenant->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return view('billing.index', compact('plans', 'subscription', 'paymentMethod'));
    }

    public function subscribe(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'payment_method_id' => 'required|string',
        ]);

        $tenant = app('currentTenant');
        $plan = Plan::findOrFail($request->plan_id);

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            // Create or retrieve customer
            if (!$tenant->stripe_id) {
                $customer = Customer::create([
                    'email' => auth()->user()->email,
                    'payment_method' => $request->payment_method_id,
                    'invoice_settings' => [
                        'default_payment_method' => $request->payment_method_id,
                    ],
                    'metadata' => [
                        'tenant_id' => $tenant->id,
                    ],
                ]);

                $tenant->update(['stripe_id' => $customer->id]);
            } else {
                $customer = Customer::retrieve($tenant->stripe_id);
                $customer->payment_method = $request->payment_method_id;
                $customer->invoice_settings->default_payment_method = $request->payment_method_id;
                $customer->save();
            }

            // Create subscription
            $subscription = Subscription::create([
                'customer' => $customer->id,
                'items' => [
                    ['price' => $plan->stripe_price_id],
                ],
                'expand' => ['latest_invoice.payment_intent'],
                'metadata' => [
                    'tenant_id' => $tenant->id,
                    'plan_id' => $plan->id,
                ],
            ]);

            // Update tenant with plan
            $tenant->update([
                'plan_id' => $plan->id,
                'trial_ends_at' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Subscription created successfully.',
                'subscription_id' => $subscription->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Subscription creation failed', [
                'error' => $e->getMessage(),
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create subscription. Please try again.',
            ], 500);
        }
    }

    public function cancelSubscription(Request $request)
    {
        $tenant = app('currentTenant');

        if (!$tenant->stripe_id) {
            return response()->json([
                'success' => false,
                'message' => 'No active subscription found.',
            ], 400);
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $customer = Customer::retrieve($tenant->stripe_id);
            $subscription = $customer->subscriptions->data[0] ?? null;

            if ($subscription) {
                $subscription->cancel_at_period_end = true;
                $subscription->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Subscription will be cancelled at the end of the current period.',
            ]);

        } catch (\Exception $e) {
            Log::error('Subscription cancellation failed', [
                'error' => $e->getMessage(),
                'tenant_id' => $tenant->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel subscription. Please try again.',
            ], 500);
        }
    }

    public function updatePaymentMethod(Request $request)
    {
        $request->validate([
            'payment_method_id' => 'required|string',
        ]);

        $tenant = app('currentTenant');

        if (!$tenant->stripe_id) {
            return response()->json([
                'success' => false,
                'message' => 'No customer found.',
            ], 400);
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $customer = Customer::retrieve($tenant->stripe_id);
            $customer->invoice_settings->default_payment_method = $request->payment_method_id;
            $customer->save();

            return response()->json([
                'success' => true,
                'message' => 'Payment method updated successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Payment method update failed', [
                'error' => $e->getMessage(),
                'tenant_id' => $tenant->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update payment method. Please try again.',
            ], 500);
        }
    }

    public function invoices()
    {
        $tenant = app('currentTenant');

        if (!$tenant->stripe_id) {
            return response()->json([]);
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $customer = Customer::retrieve($tenant->stripe_id);
            $invoices = $customer->invoices(['limit' => 12]);

            return response()->json([
                'invoices' => $invoices->data,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve invoices', [
                'error' => $e->getMessage(),
                'tenant_id' => $tenant->id,
            ]);

            return response()->json([]);
        }
    }

    public function createSetupIntent()
    {
        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $setupIntent = \Stripe\SetupIntent::create([
                'usage' => 'off_session',
            ]);

            return response()->json([
                'client_secret' => $setupIntent->client_secret,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create setup intent', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to create setup intent.',
            ], 500);
        }
    }
}