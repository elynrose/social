<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class NotificationControllerTest extends TestCase
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
    public function user_can_view_notifications_index()
    {
        $this->actingAs($this->user);
        
        $response = $this->get('/notifications');
        
        $response->assertStatus(200);
        $response->assertViewIs('notifications.index');
        $response->assertViewHas('notifications');
    }

    /** @test */
    public function user_can_view_their_notifications()
    {
        $this->actingAs($this->user);
        
        // Create notifications for the user
        Notification::factory()->count(3)->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'type' => 'post_created',
            'title' => 'New Post Created',
            'message' => 'A new post has been created'
        ]);
        
        $response = $this->get('/notifications');
        
        $response->assertStatus(200);
        $response->assertViewHas('notifications');
        $response->assertSee('New Post Created');
    }

    /** @test */
    public function user_cannot_view_other_users_notifications()
    {
        $this->actingAs($this->user);
        
        $otherUser = User::factory()->create();
        $this->tenant->users()->attach($otherUser, ['role' => 'editor']);
        
        // Create notification for other user
        $otherNotification = Notification::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $otherUser->id,
            'type' => 'post_created',
            'title' => 'Other User Notification',
            'message' => 'This should not be visible'
        ]);
        
        $response = $this->get('/notifications');
        
        $response->assertStatus(200);
        $response->assertDontSee('Other User Notification');
    }

    /** @test */
    public function user_can_mark_notification_as_read()
    {
        $this->actingAs($this->user);
        
        $notification = Notification::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'type' => 'post_created',
            'title' => 'Test Notification',
            'message' => 'Test message',
            'read_at' => null
        ]);
        
        $response = $this->patch("/api/notifications/{$notification->id}/read");
        
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'read_at' => now()->toDateTimeString()
        ]);
    }

    /** @test */
    public function user_cannot_mark_other_users_notification_as_read()
    {
        $this->actingAs($this->user);
        
        $otherUser = User::factory()->create();
        $this->tenant->users()->attach($otherUser, ['role' => 'editor']);
        
        $otherNotification = Notification::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $otherUser->id,
            'type' => 'post_created',
            'title' => 'Other User Notification',
            'message' => 'Test message'
        ]);
        
        $response = $this->patch("/api/notifications/{$otherNotification->id}/read");
        
        $response->assertStatus(403);
        $response->assertJson(['error' => 'Unauthorized']);
    }

    /** @test */
    public function user_can_mark_all_notifications_as_read()
    {
        $this->actingAs($this->user);
        
        // Create multiple unread notifications
        Notification::factory()->count(3)->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'type' => 'post_created',
            'title' => 'Test Notification',
            'message' => 'Test message',
            'read_at' => null
        ]);
        
        $response = $this->patch('/api/notifications/mark-all-read');
        
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        // Check that all notifications are now read
        $this->assertDatabaseMissing('notifications', [
            'user_id' => $this->user->id,
            'read_at' => null
        ]);
    }

    /** @test */
    public function user_can_delete_notification()
    {
        $this->actingAs($this->user);
        
        $notification = Notification::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'type' => 'post_created',
            'title' => 'Test Notification',
            'message' => 'Test message'
        ]);
        
        $response = $this->delete("/api/notifications/{$notification->id}");
        
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
    }

    /** @test */
    public function user_cannot_delete_other_users_notification()
    {
        $this->actingAs($this->user);
        
        $otherUser = User::factory()->create();
        $this->tenant->users()->attach($otherUser, ['role' => 'editor']);
        
        $otherNotification = Notification::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $otherUser->id,
            'type' => 'post_created',
            'title' => 'Other User Notification',
            'message' => 'Test message'
        ]);
        
        $response = $this->delete("/api/notifications/{$otherNotification->id}");
        
        $response->assertStatus(403);
        $response->assertJson(['error' => 'Unauthorized']);
    }

    /** @test */
    public function user_can_get_unread_count()
    {
        $this->actingAs($this->user);
        
        // Create some read and unread notifications
        Notification::factory()->count(2)->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'read_at' => null
        ]);
        
        Notification::factory()->count(1)->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'read_at' => now()
        ]);
        
        $response = $this->get('/api/notifications/unread-count');
        
        $response->assertStatus(200);
        $response->assertJson(['unread_count' => 2]);
    }

    /** @test */
    public function notifications_are_tenant_scoped()
    {
        $this->actingAs($this->user);
        
        $otherTenant = Tenant::factory()->create();
        
        // Create notification for other tenant
        Notification::factory()->create([
            'tenant_id' => $otherTenant->id,
            'user_id' => $this->user->id,
            'type' => 'post_created',
            'title' => 'Other Tenant Notification',
            'message' => 'This should not be visible'
        ]);
        
        $response = $this->get('/api/notifications/unread-count');
        
        $response->assertStatus(200);
        $response->assertJson(['unread_count' => 0]);
    }

    /** @test */
    public function notifications_api_returns_json()
    {
        $this->actingAs($this->user);
        
        Notification::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'type' => 'post_created',
            'title' => 'Test Notification',
            'message' => 'Test message'
        ]);
        
        $response = $this->get('/api/notifications');
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'notifications' => [
                '*' => [
                    'id',
                    'tenant_id',
                    'user_id',
                    'type',
                    'title',
                    'message',
                    'data',
                    'read_at',
                    'created_at',
                    'updated_at'
                ]
            ],
            'unread_count'
        ]);
    }

    /** @test */
    public function notification_data_can_contain_additional_information()
    {
        $this->actingAs($this->user);
        
        $notification = Notification::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'type' => 'post_created',
            'title' => 'Post Created',
            'message' => 'A new post has been created',
            'data' => [
                'type' => 'post_created',
                'action_url' => '/posts/123',
                'post_id' => 123
            ]
        ]);
        
        $response = $this->get('/notifications');
        
        $response->assertStatus(200);
        $response->assertSee('post_created');
        $response->assertSee('/posts/123');
    }
} 