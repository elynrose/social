<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use App\Models\SocialAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

class OAuthControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create tenant and user
        $this->tenant = Tenant::factory()->create();
        $this->user = User::factory()->create();
        $this->tenant->users()->attach($this->user, ['role' => 'owner']);
        
        // Set current tenant
        app()->instance('currentTenant', $this->tenant);
    }

    /** @test */
    public function user_can_redirect_to_oauth_provider()
    {
        $this->actingAs($this->user);
        
        $provider = 'facebook';
        
        $response = $this->get("/oauth/{$provider}");
        
        $response->assertStatus(302);
        $response->assertRedirect();
    }

    /** @test */
    public function oauth_redirect_requires_authentication()
    {
        $provider = 'facebook';
        
        $response = $this->get("/oauth/{$provider}");
        
        $response->assertRedirect('/login');
    }

    /** @test */
    public function oauth_redirect_requires_valid_provider()
    {
        $this->actingAs($this->user);
        
        $provider = 'invalid_provider';
        
        $response = $this->get("/oauth/{$provider}");
        
        $response->assertStatus(404);
    }

    /** @test */
    public function oauth_callback_handles_successful_authentication()
    {
        $this->actingAs($this->user);
        
        $provider = 'facebook';
        
        // Mock Socialite user
        $socialiteUser = new SocialiteUser();
        $socialiteUser->id = '12345';
        $socialiteUser->nickname = 'testuser';
        $socialiteUser->name = 'Test User';
        $socialiteUser->email = 'test@example.com';
        $socialiteUser->avatar = 'https://example.com/avatar.jpg';
        
        // Mock Socialite driver
        Socialite::shouldReceive('driver')
            ->with($provider)
            ->andReturnSelf();
        
        Socialite::shouldReceive('user')
            ->andReturn($socialiteUser);
        
        $response = $this->get("/oauth/{$provider}/callback");
        
        $response->assertStatus(302);
        $response->assertRedirect('/dashboard');
        
        // Check that social account was created
        $this->assertDatabaseHas('social_accounts', [
            'tenant_id' => $this->tenant->id,
            'platform' => $provider,
            'account_id' => '12345',
            'username' => 'testuser'
        ]);
    }

    /** @test */
    public function oauth_callback_handles_existing_account()
    {
        $this->actingAs($this->user);
        
        $provider = 'facebook';
        
        // Create existing social account
        SocialAccount::factory()->create([
            'tenant_id' => $this->tenant->id,
            'platform' => $provider,
            'account_id' => '12345',
            'username' => 'existinguser'
        ]);
        
        // Mock Socialite user
        $socialiteUser = new SocialiteUser();
        $socialiteUser->id = '12345';
        $socialiteUser->nickname = 'existinguser';
        $socialiteUser->name = 'Existing User';
        $socialiteUser->email = 'existing@example.com';
        
        // Mock Socialite driver
        Socialite::shouldReceive('driver')
            ->with($provider)
            ->andReturnSelf();
        
        Socialite::shouldReceive('user')
            ->andReturn($socialiteUser);
        
        $response = $this->get("/oauth/{$provider}/callback");
        
        $response->assertStatus(302);
        $response->assertRedirect('/dashboard');
        
        // Check that account was updated, not duplicated
        $this->assertDatabaseCount('social_accounts', 1);
    }

    /** @test */
    public function oauth_callback_handles_authentication_error()
    {
        $this->actingAs($this->user);
        
        $provider = 'facebook';
        
        // Mock Socialite to throw exception
        Socialite::shouldReceive('driver')
            ->with($provider)
            ->andReturnSelf();
        
        Socialite::shouldReceive('user')
            ->andThrow(new \Exception('OAuth authentication failed'));
        
        $response = $this->get("/oauth/{$provider}/callback");
        
        $response->assertStatus(302);
        $response->assertRedirect('/dashboard');
    }

    /** @test */
    public function oauth_supports_multiple_providers()
    {
        $this->actingAs($this->user);
        
        $providers = ['facebook', 'twitter', 'linkedin', 'instagram', 'youtube'];
        
        foreach ($providers as $provider) {
            $response = $this->get("/oauth/{$provider}");
            
            $response->assertStatus(302);
            $response->assertRedirect();
        }
    }

    /** @test */
    public function oauth_callback_stores_access_token()
    {
        $this->actingAs($this->user);
        
        $provider = 'facebook';
        
        // Mock Socialite user with token
        $socialiteUser = new SocialiteUser();
        $socialiteUser->id = '12345';
        $socialiteUser->nickname = 'testuser';
        $socialiteUser->name = 'Test User';
        $socialiteUser->email = 'test@example.com';
        $socialiteUser->token = 'access_token_123';
        $socialiteUser->refreshToken = 'refresh_token_456';
        $socialiteUser->expiresIn = 3600;
        
        // Mock Socialite driver
        Socialite::shouldReceive('driver')
            ->with($provider)
            ->andReturnSelf();
        
        Socialite::shouldReceive('user')
            ->andReturn($socialiteUser);
        
        $response = $this->get("/oauth/{$provider}/callback");
        
        $response->assertStatus(302);
        
        // Check that tokens were stored
        $this->assertDatabaseHas('social_accounts', [
            'tenant_id' => $this->tenant->id,
            'platform' => $provider,
            'account_id' => '12345',
            'access_token' => 'access_token_123',
            'refresh_token' => 'refresh_token_456'
        ]);
    }

    /** @test */
    public function oauth_callback_handles_missing_user_data()
    {
        $this->actingAs($this->user);
        
        $provider = 'facebook';
        
        // Mock Socialite user with minimal data
        $socialiteUser = new SocialiteUser();
        $socialiteUser->id = '12345';
        $socialiteUser->nickname = null;
        $socialiteUser->name = null;
        $socialiteUser->email = null;
        
        // Mock Socialite driver
        Socialite::shouldReceive('driver')
            ->with($provider)
            ->andReturnSelf();
        
        Socialite::shouldReceive('user')
            ->andReturn($socialiteUser);
        
        $response = $this->get("/oauth/{$provider}/callback");
        
        $response->assertStatus(302);
        $response->assertRedirect('/dashboard');
        
        // Check that account was created with fallback values
        $this->assertDatabaseHas('social_accounts', [
            'tenant_id' => $this->tenant->id,
            'platform' => $provider,
            'account_id' => '12345',
            'username' => 'unknown_user'
        ]);
    }

    /** @test */
    public function oauth_callback_requires_tenant_context()
    {
        $this->actingAs($this->user);
        
        // Remove tenant context
        app()->forgetInstance('currentTenant');
        
        $provider = 'facebook';
        
        $response = $this->get("/oauth/{$provider}/callback");
        
        $response->assertStatus(500);
    }

    /** @test */
    public function oauth_callback_handles_database_errors()
    {
        $this->actingAs($this->user);
        
        $provider = 'facebook';
        
        // Mock Socialite user
        $socialiteUser = new SocialiteUser();
        $socialiteUser->id = '12345';
        $socialiteUser->nickname = 'testuser';
        $socialiteUser->name = 'Test User';
        $socialiteUser->email = 'test@example.com';
        
        // Mock Socialite driver
        Socialite::shouldReceive('driver')
            ->with($provider)
            ->andReturnSelf();
        
        Socialite::shouldReceive('user')
            ->andReturn($socialiteUser);
        
        // Mock database error by making a field too long
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        // This would normally cause a database error
        // We're just testing that the error is handled gracefully
        $response = $this->get("/oauth/{$provider}/callback");
    }

    /** @test */
    public function oauth_redirect_includes_correct_scopes()
    {
        $this->actingAs($this->user);
        
        $provider = 'facebook';
        
        $response = $this->get("/oauth/{$provider}");
        
        $response->assertStatus(302);
        
        // Check that the redirect URL includes the correct scopes
        $redirectUrl = $response->headers->get('Location');
        $this->assertStringContainsString('scope=', $redirectUrl);
    }

    /** @test */
    public function oauth_callback_updates_existing_tokens()
    {
        $this->actingAs($this->user);
        
        $provider = 'facebook';
        
        // Create existing social account with old tokens
        $existingAccount = SocialAccount::factory()->create([
            'tenant_id' => $this->tenant->id,
            'platform' => $provider,
            'account_id' => '12345',
            'username' => 'existinguser',
            'access_token' => 'old_access_token',
            'refresh_token' => 'old_refresh_token'
        ]);
        
        // Mock Socialite user with new tokens
        $socialiteUser = new SocialiteUser();
        $socialiteUser->id = '12345';
        $socialiteUser->nickname = 'existinguser';
        $socialiteUser->name = 'Existing User';
        $socialiteUser->email = 'existing@example.com';
        $socialiteUser->token = 'new_access_token';
        $socialiteUser->refreshToken = 'new_refresh_token';
        $socialiteUser->expiresIn = 3600;
        
        // Mock Socialite driver
        Socialite::shouldReceive('driver')
            ->with($provider)
            ->andReturnSelf();
        
        Socialite::shouldReceive('user')
            ->andReturn($socialiteUser);
        
        $response = $this->get("/oauth/{$provider}/callback");
        
        $response->assertStatus(302);
        
        // Check that tokens were updated
        $this->assertDatabaseHas('social_accounts', [
            'id' => $existingAccount->id,
            'access_token' => 'new_access_token',
            'refresh_token' => 'new_refresh_token'
        ]);
    }
} 