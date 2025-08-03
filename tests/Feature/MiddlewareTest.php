<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use App\Http\Middleware\SetCurrentTenant;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\HttpsRedirect;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;

class MiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create tenant and user
        $this->tenant = Tenant::factory()->create();
        $this->user = User::factory()->create();
        $this->tenant->users()->attach($this->user, ['role' => 'owner']);
    }

    /** @test */
    public function set_current_tenant_middleware_sets_tenant_context()
    {
        $middleware = new SetCurrentTenant();
        
        $request = Request::create('/test', 'GET');
        $request->setUserResolver(function () {
            return $this->user;
        });
        
        $response = new Response();
        
        $middleware->handle($request, function ($req) {
            $this->assertNotNull(app('currentTenant'));
            $this->assertEquals($this->tenant->id, app('currentTenant')->id);
            return new Response();
        });
    }

    /** @test */
    public function set_current_tenant_middleware_handles_no_user()
    {
        $middleware = new SetCurrentTenant();
        
        $request = Request::create('/test', 'GET');
        $request->setUserResolver(function () {
            return null;
        });
        
        $response = new Response();
        
        $middleware->handle($request, function ($req) {
            $this->assertNull(app('currentTenant'));
            return new Response();
        });
    }

    /** @test */
    public function set_current_tenant_middleware_handles_user_without_tenant()
    {
        $middleware = new SetCurrentTenant();
        
        $userWithoutTenant = User::factory()->create();
        
        $request = Request::create('/test', 'GET');
        $request->setUserResolver(function () use ($userWithoutTenant) {
            return $userWithoutTenant;
        });
        
        $response = new Response();
        
        $middleware->handle($request, function ($req) {
            $this->assertNull(app('currentTenant'));
            return new Response();
        });
    }

    /** @test */
    public function security_headers_middleware_adds_required_headers()
    {
        $middleware = new SecurityHeaders();
        
        $request = Request::create('/test', 'GET');
        $response = new Response();
        
        $response = $middleware->handle($request, function ($req) {
            return new Response();
        });
        
        $this->assertTrue($response->headers->has('X-Frame-Options'));
        $this->assertTrue($response->headers->has('X-Content-Type-Options'));
        $this->assertTrue($response->headers->has('X-XSS-Protection'));
        $this->assertTrue($response->headers->has('Referrer-Policy'));
        $this->assertTrue($response->headers->has('Permissions-Policy'));
        $this->assertTrue($response->headers->has('Content-Security-Policy'));
        
        $this->assertEquals('DENY', $response->headers->get('X-Frame-Options'));
        $this->assertEquals('nosniff', $response->headers->get('X-Content-Type-Options'));
        $this->assertEquals('1; mode=block', $response->headers->get('X-XSS-Protection'));
        $this->assertEquals('strict-origin-when-cross-origin', $response->headers->get('Referrer-Policy'));
    }

    /** @test */
    public function https_redirect_middleware_redirects_in_production()
    {
        $middleware = new HttpsRedirect();
        
        $request = Request::create('http://example.com/test', 'GET');
        
        // Set app environment to production
        App::shouldReceive('environment')
            ->andReturn('production');
        
        $response = $middleware->handle($request, function ($req) {
            return new Response();
        });
        
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertStringContainsString('https://', $response->headers->get('Location'));
    }

    /** @test */
    public function https_redirect_middleware_does_not_redirect_in_local()
    {
        $middleware = new HttpsRedirect();
        
        $request = Request::create('http://example.com/test', 'GET');
        
        // Set app environment to local
        App::shouldReceive('environment')
            ->andReturn('local');
        
        $response = $middleware->handle($request, function ($req) {
            return new Response();
        });
        
        $this->assertEquals(200, $response->getStatusCode());
    }

    /** @test */
    public function https_redirect_middleware_does_not_redirect_https_requests()
    {
        $middleware = new HttpsRedirect();
        
        $request = Request::create('https://example.com/test', 'GET');
        
        // Set app environment to production
        App::shouldReceive('environment')
            ->andReturn('production');
        
        $response = $middleware->handle($request, function ($req) {
            return new Response();
        });
        
        $this->assertEquals(200, $response->getStatusCode());
    }

    /** @test */
    public function set_current_tenant_middleware_handles_multiple_tenants()
    {
        $middleware = new SetCurrentTenant();
        
        // Create second tenant and user
        $tenant2 = Tenant::factory()->create();
        $user2 = User::factory()->create();
        $tenant2->users()->attach($user2, ['role' => 'owner']);
        
        $request = Request::create('/test', 'GET');
        $request->setUserResolver(function () use ($user2) {
            return $user2;
        });
        
        $middleware->handle($request, function ($req) use ($tenant2) {
            $this->assertNotNull(app('currentTenant'));
            $this->assertEquals($tenant2->id, app('currentTenant')->id);
            return new Response();
        });
    }

    /** @test */
    public function set_current_tenant_middleware_handles_user_with_multiple_tenants()
    {
        $middleware = new SetCurrentTenant();
        
        // Create second tenant and attach user to both
        $tenant2 = Tenant::factory()->create();
        $this->tenant->users()->attach($this->user, ['role' => 'owner']);
        $tenant2->users()->attach($this->user, ['role' => 'editor']);
        
        $request = Request::create('/test', 'GET');
        $request->setUserResolver(function () {
            return $this->user;
        });
        
        $middleware->handle($request, function ($req) {
            $this->assertNotNull(app('currentTenant'));
            // Should use the first tenant (most recent or primary)
            $this->assertEquals($this->tenant->id, app('currentTenant')->id);
            return new Response();
        });
    }

    /** @test */
    public function security_headers_middleware_has_correct_csp_policy()
    {
        $middleware = new SecurityHeaders();
        
        $request = Request::create('/test', 'GET');
        $response = new Response();
        
        $response = $middleware->handle($request, function ($req) {
            return new Response();
        });
        
        $csp = $response->headers->get('Content-Security-Policy');
        
        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("script-src 'self' 'unsafe-inline' 'unsafe-eval'", $csp);
        $this->assertStringContainsString("style-src 'self' 'unsafe-inline'", $csp);
        $this->assertStringContainsString("img-src 'self' data: https:", $csp);
        $this->assertStringContainsString("font-src 'self'", $csp);
        $this->assertStringContainsString("connect-src 'self'", $csp);
    }

    /** @test */
    public function security_headers_middleware_has_correct_permissions_policy()
    {
        $middleware = new SecurityHeaders();
        
        $request = Request::create('/test', 'GET');
        $response = new Response();
        
        $response = $middleware->handle($request, function ($req) {
            return new Response();
        });
        
        $permissionsPolicy = $response->headers->get('Permissions-Policy');
        
        $this->assertStringContainsString('camera=()', $permissionsPolicy);
        $this->assertStringContainsString('microphone=()', $permissionsPolicy);
        $this->assertStringContainsString('geolocation=()', $permissionsPolicy);
        $this->assertStringContainsString('payment=()', $permissionsPolicy);
    }

    /** @test */
    public function https_redirect_middleware_handles_different_urls()
    {
        $middleware = new HttpsRedirect();
        
        $urls = [
            'http://example.com/',
            'http://example.com/posts',
            'http://example.com/api/notifications',
            'http://example.com/webhooks/stripe'
        ];
        
        // Set app environment to production
        App::shouldReceive('environment')
            ->andReturn('production');
        
        foreach ($urls as $url) {
            $request = Request::create($url, 'GET');
            
            $response = $middleware->handle($request, function ($req) {
                return new Response();
            });
            
            $this->assertEquals(302, $response->getStatusCode());
            $this->assertStringContainsString('https://', $response->headers->get('Location'));
        }
    }

    /** @test */
    public function set_current_tenant_middleware_preserves_existing_tenant()
    {
        $middleware = new SetCurrentTenant();
        
        // Set existing tenant
        app()->instance('currentTenant', $this->tenant);
        
        $request = Request::create('/test', 'GET');
        $request->setUserResolver(function () {
            return $this->user;
        });
        
        $middleware->handle($request, function ($req) {
            $this->assertNotNull(app('currentTenant'));
            $this->assertEquals($this->tenant->id, app('currentTenant')->id);
            return new Response();
        });
    }

    /** @test */
    public function security_headers_middleware_works_with_json_responses()
    {
        $middleware = new SecurityHeaders();
        
        $request = Request::create('/api/test', 'GET');
        $request->headers->set('Accept', 'application/json');
        
        $response = $middleware->handle($request, function ($req) {
            return response()->json(['message' => 'test']);
        });
        
        $this->assertTrue($response->headers->has('X-Frame-Options'));
        $this->assertTrue($response->headers->has('Content-Security-Policy'));
    }

    /** @test */
    public function https_redirect_middleware_handles_post_requests()
    {
        $middleware = new HttpsRedirect();
        
        $request = Request::create('http://example.com/posts', 'POST');
        
        // Set app environment to production
        App::shouldReceive('environment')
            ->andReturn('production');
        
        $response = $middleware->handle($request, function ($req) {
            return new Response();
        });
        
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertStringContainsString('https://', $response->headers->get('Location'));
    }
} 