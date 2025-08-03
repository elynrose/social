<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;

class WebhookControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $tenant;
    protected $plan;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create tenant and plan
        $this->tenant = Tenant::factory()->create([
            'stripe_id' => 'cus_test123'
        ]);
        
        $this->plan = Plan::factory()->create([
            'name' => 'Pro Plan',
            'price' => 29.99,
            'stripe_price_id' => 'price_test123'
        ]);
        
        // Disable events for testing
        Event::fake();
    }

    /** @test */
    public function webhook_handles_subscription_created()
    {
        $webhookData = [
            'type' => 'customer.subscription.created',
            'data' => [
                'object' => [
                    'id' => 'sub_test123',
                    'customer' => 'cus_test123',
                    'status' => 'active',
                    'current_period_end' => time() + 86400,
                    'items' => [
                        'data' => [
                            [
                                'price' => [
                                    'id' => 'price_test123'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        
        $response = $this->post('/webhooks/stripe', $webhookData, [
            'Stripe-Signature' => 'test_signature'
        ]);
        
        $response->assertStatus(200);
        
        // Check that tenant was updated
        $this->tenant->refresh();
        $this->assertEquals($this->plan->id, $this->tenant->plan_id);
    }

    /** @test */
    public function webhook_handles_subscription_updated()
    {
        // Set up tenant with existing subscription
        $this->tenant->update([
            'plan_id' => $this->plan->id
        ]);
        
        $webhookData = [
            'type' => 'customer.subscription.updated',
            'data' => [
                'object' => [
                    'id' => 'sub_test123',
                    'customer' => 'cus_test123',
                    'status' => 'active',
                    'current_period_end' => time() + 86400,
                    'items' => [
                        'data' => [
                            [
                                'price' => [
                                    'id' => 'price_test123'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        
        $response = $this->post('/webhooks/stripe', $webhookData, [
            'Stripe-Signature' => 'test_signature'
        ]);
        
        $response->assertStatus(200);
    }

    /** @test */
    public function webhook_handles_subscription_deleted()
    {
        // Set up tenant with existing subscription
        $this->tenant->update([
            'plan_id' => $this->plan->id
        ]);
        
        $webhookData = [
            'type' => 'customer.subscription.deleted',
            'data' => [
                'object' => [
                    'id' => 'sub_test123',
                    'customer' => 'cus_test123',
                    'status' => 'canceled'
                ]
            ]
        ];
        
        $response = $this->post('/webhooks/stripe', $webhookData, [
            'Stripe-Signature' => 'test_signature'
        ]);
        
        $response->assertStatus(200);
        
        // Check that tenant plan was removed
        $this->tenant->refresh();
        $this->assertNull($this->tenant->plan_id);
    }

    /** @test */
    public function webhook_handles_payment_succeeded()
    {
        $webhookData = [
            'type' => 'invoice.payment_succeeded',
            'data' => [
                'object' => [
                    'id' => 'in_test123',
                    'customer' => 'cus_test123',
                    'subscription' => 'sub_test123',
                    'amount_paid' => 2999,
                    'status' => 'paid'
                ]
            ]
        ];
        
        $response = $this->post('/webhooks/stripe', $webhookData, [
            'Stripe-Signature' => 'test_signature'
        ]);
        
        $response->assertStatus(200);
    }

    /** @test */
    public function webhook_handles_payment_failed()
    {
        $webhookData = [
            'type' => 'invoice.payment_failed',
            'data' => [
                'object' => [
                    'id' => 'in_test123',
                    'customer' => 'cus_test123',
                    'subscription' => 'sub_test123',
                    'amount_due' => 2999,
                    'status' => 'open'
                ]
            ]
        ];
        
        $response = $this->post('/webhooks/stripe', $webhookData, [
            'Stripe-Signature' => 'test_signature'
        ]);
        
        $response->assertStatus(200);
    }

    /** @test */
    public function webhook_requires_stripe_signature()
    {
        $webhookData = [
            'type' => 'customer.subscription.created',
            'data' => [
                'object' => [
                    'id' => 'sub_test123',
                    'customer' => 'cus_test123'
                ]
            ]
        ];
        
        $response = $this->post('/webhooks/stripe', $webhookData);
        
        $response->assertStatus(400);
    }

    /** @test */
    public function webhook_handles_unknown_event_type()
    {
        $webhookData = [
            'type' => 'unknown.event.type',
            'data' => [
                'object' => [
                    'id' => 'test123'
                ]
            ]
        ];
        
        $response = $this->post('/webhooks/stripe', $webhookData, [
            'Stripe-Signature' => 'test_signature'
        ]);
        
        $response->assertStatus(200);
    }

    /** @test */
    public function webhook_handles_missing_customer()
    {
        $webhookData = [
            'type' => 'customer.subscription.created',
            'data' => [
                'object' => [
                    'id' => 'sub_test123',
                    'customer' => 'cus_nonexistent',
                    'status' => 'active'
                ]
            ]
        ];
        
        $response = $this->post('/webhooks/stripe', $webhookData, [
            'Stripe-Signature' => 'test_signature'
        ]);
        
        $response->assertStatus(200);
    }

    /** @test */
    public function webhook_handles_malformed_data()
    {
        $webhookData = [
            'type' => 'customer.subscription.created',
            'data' => [
                'object' => [
                    // Missing required fields
                ]
            ]
        ];
        
        $response = $this->post('/webhooks/stripe', $webhookData, [
            'Stripe-Signature' => 'test_signature'
        ]);
        
        $response->assertStatus(200);
    }

    /** @test */
    public function webhook_handles_trial_events()
    {
        $webhookData = [
            'type' => 'customer.subscription.trial_will_end',
            'data' => [
                'object' => [
                    'id' => 'sub_test123',
                    'customer' => 'cus_test123',
                    'trial_end' => time() + 86400
                ]
            ]
        ];
        
        $response = $this->post('/webhooks/stripe', $webhookData, [
            'Stripe-Signature' => 'test_signature'
        ]);
        
        $response->assertStatus(200);
    }

    /** @test */
    public function webhook_handles_invoice_events()
    {
        $webhookData = [
            'type' => 'invoice.created',
            'data' => [
                'object' => [
                    'id' => 'in_test123',
                    'customer' => 'cus_test123',
                    'subscription' => 'sub_test123',
                    'amount_due' => 2999
                ]
            ]
        ];
        
        $response = $this->post('/webhooks/stripe', $webhookData, [
            'Stripe-Signature' => 'test_signature'
        ]);
        
        $response->assertStatus(200);
    }

    /** @test */
    public function webhook_handles_customer_events()
    {
        $webhookData = [
            'type' => 'customer.updated',
            'data' => [
                'object' => [
                    'id' => 'cus_test123',
                    'email' => 'test@example.com',
                    'name' => 'Test Customer'
                ]
            ]
        ];
        
        $response = $this->post('/webhooks/stripe', $webhookData, [
            'Stripe-Signature' => 'test_signature'
        ]);
        
        $response->assertStatus(200);
    }

    /** @test */
    public function webhook_handles_payment_method_events()
    {
        $webhookData = [
            'type' => 'payment_method.attached',
            'data' => [
                'object' => [
                    'id' => 'pm_test123',
                    'customer' => 'cus_test123',
                    'type' => 'card',
                    'card' => [
                        'last4' => '4242'
                    ]
                ]
            ]
        ];
        
        $response = $this->post('/webhooks/stripe', $webhookData, [
            'Stripe-Signature' => 'test_signature'
        ]);
        
        $response->assertStatus(200);
    }

    /** @test */
    public function webhook_handles_charge_events()
    {
        $webhookData = [
            'type' => 'charge.succeeded',
            'data' => [
                'object' => [
                    'id' => 'ch_test123',
                    'customer' => 'cus_test123',
                    'amount' => 2999,
                    'currency' => 'usd',
                    'status' => 'succeeded'
                ]
            ]
        ];
        
        $response = $this->post('/webhooks/stripe', $webhookData, [
            'Stripe-Signature' => 'test_signature'
        ]);
        
        $response->assertStatus(200);
    }

    /** @test */
    public function webhook_handles_refund_events()
    {
        $webhookData = [
            'type' => 'charge.refunded',
            'data' => [
                'object' => [
                    'id' => 'ch_test123',
                    'customer' => 'cus_test123',
                    'amount_refunded' => 2999,
                    'refunds' => [
                        'data' => [
                            [
                                'id' => 're_test123',
                                'amount' => 2999
                            ]
                        ]
                    ]
                ]
            ]
        ];
        
        $response = $this->post('/webhooks/stripe', $webhookData, [
            'Stripe-Signature' => 'test_signature'
        ]);
        
        $response->assertStatus(200);
    }

    /** @test */
    public function webhook_handles_dispute_events()
    {
        $webhookData = [
            'type' => 'charge.dispute.created',
            'data' => [
                'object' => [
                    'id' => 'dp_test123',
                    'charge' => 'ch_test123',
                    'customer' => 'cus_test123',
                    'amount' => 2999,
                    'status' => 'needs_response'
                ]
            ]
        ];
        
        $response = $this->post('/webhooks/stripe', $webhookData, [
            'Stripe-Signature' => 'test_signature'
        ]);
        
        $response->assertStatus(200);
    }
} 