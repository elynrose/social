<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Config;

class BillingControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $tenant;
    protected $plan;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create tenant and user
        $this->tenant = Tenant::factory()->create();
        $this->user = User::factory()->create();
        $this->tenant->users()->attach($this->user, ['role' => 'owner']);
        
        // Create a plan
        $this->plan = Plan::factory()->create([
            'name' => 'Pro Plan',
            'price' => 29.99,
            'stripe_price_id' => 'price_test123'
        ]);
        
        // Set current tenant
        app()->instance('currentTenant', $this->tenant);
        
        // Mock Stripe configuration
        Config::set('cashier.key', 'pk_test_123');
        Config::set('cashier.secret', 'sk_test_123');
    }

    /** @test */
    public function user_can_view_billing_page()
    {
        $this->actingAs($this->user);
        
        $response = $this->get('/billing');
        
        $response->assertStatus(200);
        $response->assertViewIs('billing.index');
        $response->assertViewHas('plans');
    }

    /** @test */
    public function billing_page_shows_available_plans()
    {
        $this->actingAs($this->user);
        
        // Create multiple plans
        Plan::factory()->count(3)->create();
        
        $response = $this->get('/billing');
        
        $response->assertStatus(200);
        $response->assertSee('Pro Plan');
    }

    /** @test */
    public function user_can_subscribe_to_plan()
    {
        $this->actingAs($this->user);
        
        $subscriptionData = [
            'plan_id' => $this->plan->id,
            'payment_method' => 'pm_test_123'
        ];
        
        $response = $this->post('/api/subscribe', $subscriptionData);
        
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'subscription_id']);
    }

    /** @test */
    public function subscription_requires_valid_plan()
    {
        $this->actingAs($this->user);
        
        $subscriptionData = [
            'plan_id' => 999, // Non-existent plan
            'payment_method' => 'pm_test_123'
        ];
        
        $response = $this->post('/api/subscribe', $subscriptionData);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['plan_id']);
    }

    /** @test */
    public function subscription_requires_payment_method()
    {
        $this->actingAs($this->user);
        
        $subscriptionData = [
            'plan_id' => $this->plan->id
            // Missing payment_method
        ];
        
        $response = $this->post('/api/subscribe', $subscriptionData);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['payment_method']);
    }

    /** @test */
    public function user_can_cancel_subscription()
    {
        $this->actingAs($this->user);
        
        // Mock tenant with subscription
        $this->tenant->update([
            'stripe_id' => 'cus_test123',
            'pm_type' => 'card',
            'pm_last_four' => '4242'
        ]);
        
        $response = $this->post('/api/billing/cancel-subscription');
        
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    /** @test */
    public function user_can_update_payment_method()
    {
        $this->actingAs($this->user);
        
        // Mock tenant with subscription
        $this->tenant->update([
            'stripe_id' => 'cus_test123',
            'pm_type' => 'card',
            'pm_last_four' => '4242'
        ]);
        
        $paymentData = [
            'payment_method' => 'pm_test_new123'
        ];
        
        $response = $this->patch('/api/billing/payment-method', $paymentData);
        
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    /** @test */
    public function user_can_view_invoices()
    {
        $this->actingAs($this->user);
        
        // Mock tenant with subscription
        $this->tenant->update([
            'stripe_id' => 'cus_test123'
        ]);
        
        $response = $this->get('/api/billing/invoices');
        
        $response->assertStatus(200);
        $response->assertJsonStructure(['invoices']);
    }

    /** @test */
    public function user_can_create_setup_intent()
    {
        $this->actingAs($this->user);
        
        $response = $this->post('/api/billing/setup-intent');
        
        $response->assertStatus(200);
        $response->assertJsonStructure(['client_secret']);
    }

    /** @test */
    public function billing_requires_authentication()
    {
        $response = $this->get('/billing');
        
        $response->assertRedirect('/login');
    }

    /** @test */
    public function billing_requires_tenant_context()
    {
        $this->actingAs($this->user);
        
        // Remove tenant context
        app()->forgetInstance('currentTenant');
        
        $response = $this->get('/billing');
        
        $response->assertStatus(500);
    }

    /** @test */
    public function plan_has_required_fields()
    {
        $this->actingAs($this->user);
        
        $plan = Plan::factory()->create([
            'name' => 'Test Plan',
            'price' => 19.99,
            'stripe_price_id' => 'price_test456',
            'features' => ['feature1', 'feature2']
        ]);
        
        $response = $this->get('/billing');
        
        $response->assertStatus(200);
        $response->assertSee('Test Plan');
        $response->assertSee('19.99');
    }

    /** @test */
    public function subscription_updates_tenant_plan()
    {
        $this->actingAs($this->user);
        
        $subscriptionData = [
            'plan_id' => $this->plan->id,
            'payment_method' => 'pm_test_123'
        ];
        
        $response = $this->post('/api/subscribe', $subscriptionData);
        
        $response->assertStatus(200);
        
        // Check that tenant plan was updated
        $this->tenant->refresh();
        $this->assertEquals($this->plan->id, $this->tenant->plan_id);
    }

    /** @test */
    public function billing_api_endpoints_require_authentication()
    {
        $response = $this->post('/api/subscribe', []);
        
        $response->assertStatus(401);
    }

    /** @test */
    public function billing_api_endpoints_require_tenant_context()
    {
        $this->actingAs($this->user);
        
        // Remove tenant context
        app()->forgetInstance('currentTenant');
        
        $response = $this->post('/api/subscribe', []);
        
        $response->assertStatus(500);
    }
} 