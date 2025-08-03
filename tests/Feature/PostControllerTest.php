<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Post;
use App\Models\SocialAccount;
use App\Models\Campaign;
use App\Models\Comment;
use App\Models\Approval;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PostControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $tenant;
    protected $socialAccount;
    protected $campaign;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create tenant and user
        $this->tenant = Tenant::factory()->create();
        $this->user = User::factory()->create();
        $this->tenant->users()->attach($this->user, ['role' => 'owner']);
        
        // Create social account and campaign
        $this->socialAccount = SocialAccount::factory()->create([
            'tenant_id' => $this->tenant->id,
            'platform' => 'facebook',
            'username' => 'testaccount'
        ]);
        
        $this->campaign = Campaign::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Test Campaign'
        ]);

        // Set current tenant
        app()->instance('currentTenant', $this->tenant);
        
        // Fake storage
        Storage::fake('public');
    }

    /** @test */
    public function user_can_view_posts_index()
    {
        $this->actingAs($this->user);
        
        $response = $this->get('/posts');
        
        $response->assertStatus(200);
        $response->assertViewIs('posts.index');
    }

    /** @test */
    public function user_can_view_create_post_form()
    {
        $this->actingAs($this->user);
        
        $response = $this->get('/posts/create');
        
        $response->assertStatus(200);
        $response->assertViewIs('posts.create');
        $response->assertViewHas('campaigns');
        $response->assertViewHas('socialAccounts');
    }

    /** @test */
    public function user_can_create_post_without_media()
    {
        $this->actingAs($this->user);
        
        $postData = [
            'content' => 'Test post content',
            'platforms' => ['facebook'],
            'social_account_id' => $this->socialAccount->id,
            'campaign_id' => $this->campaign->id,
            'status' => 'draft'
        ];
        
        $response = $this->post('/posts', $postData);
        
        $response->assertRedirect('/posts');
        $this->assertDatabaseHas('posts', [
            'content' => 'Test post content',
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'social_account_id' => $this->socialAccount->id,
            'campaign_id' => $this->campaign->id,
            'status' => 'draft'
        ]);
    }

    /** @test */
    public function user_can_create_post_with_media()
    {
        $this->actingAs($this->user);
        
        $file = UploadedFile::fake()->image('test.jpg', 100, 100);
        
        $postData = [
            'content' => 'Test post with media',
            'platforms' => ['facebook'],
            'social_account_id' => $this->socialAccount->id,
            'media' => $file,
            'alt_text' => 'Test image description'
        ];
        
        $response = $this->post('/posts', $postData);
        
        $response->assertRedirect('/posts');
        $this->assertDatabaseHas('posts', [
            'content' => 'Test post with media',
            'alt_text' => 'Test image description'
        ]);
        
        // Check if file was stored
        $post = Post::where('content', 'Test post with media')->first();
        $this->assertNotNull($post->media_path);
        Storage::disk('public')->assertExists($post->media_path);
    }

    /** @test */
    public function user_can_view_post_details()
    {
        $this->actingAs($this->user);
        
        $post = Post::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'social_account_id' => $this->socialAccount->id,
            'content' => 'Test post for viewing'
        ]);
        
        $response = $this->get("/posts/{$post->id}");
        
        $response->assertStatus(200);
        $response->assertViewIs('posts.show');
        $response->assertViewHas('post');
    }

    /** @test */
    public function user_can_view_edit_post_form()
    {
        $this->actingAs($this->user);
        
        $post = Post::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'social_account_id' => $this->socialAccount->id,
            'content' => 'Test post for editing'
        ]);
        
        $response = $this->get("/posts/{$post->id}/edit");
        
        $response->assertStatus(200);
        $response->assertViewIs('posts.edit');
        $response->assertViewHas('post');
        $response->assertViewHas('campaigns');
        $response->assertViewHas('socialAccounts');
    }

    /** @test */
    public function user_can_update_post()
    {
        $this->actingAs($this->user);
        
        $post = Post::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'social_account_id' => $this->socialAccount->id,
            'content' => 'Original content'
        ]);
        
        $updateData = [
            'content' => 'Updated content',
            'alt_text' => 'Updated alt text',
            'status' => 'published'
        ];
        
        $response = $this->patch("/posts/{$post->id}", $updateData);
        
        $response->assertRedirect('/posts');
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'content' => 'Updated content',
            'alt_text' => 'Updated alt text',
            'status' => 'published'
        ]);
    }

    /** @test */
    public function user_can_delete_post()
    {
        $this->actingAs($this->user);
        
        $post = Post::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'social_account_id' => $this->socialAccount->id,
            'content' => 'Post to delete'
        ]);
        
        $response = $this->delete("/posts/{$post->id}");
        
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    /** @test */
    public function user_cannot_access_other_tenants_posts()
    {
        $this->actingAs($this->user);
        
        $otherTenant = Tenant::factory()->create();
        $otherPost = Post::factory()->create([
            'tenant_id' => $otherTenant->id,
            'user_id' => $this->user->id,
            'content' => 'Other tenant post'
        ]);
        
        $response = $this->get("/posts/{$otherPost->id}");
        
        $response->assertStatus(403);
    }

    /** @test */
    public function post_creation_validates_required_fields()
    {
        $this->actingAs($this->user);
        
        $response = $this->post('/posts', []);
        
        $response->assertSessionHasErrors(['content', 'platforms']);
    }

    /** @test */
    public function post_creation_validates_media_file_size()
    {
        $this->actingAs($this->user);
        
        $largeFile = UploadedFile::fake()->create('large.jpg', 11000); // 11MB
        
        $postData = [
            'content' => 'Test post',
            'platforms' => ['facebook'],
            'media' => $largeFile
        ];
        
        $response = $this->post('/posts', $postData);
        
        $response->assertSessionHasErrors(['media']);
    }

    /** @test */
    public function user_can_duplicate_post()
    {
        $this->actingAs($this->user);
        
        $post = Post::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'social_account_id' => $this->socialAccount->id,
            'content' => 'Original post'
        ]);
        
        $response = $this->post("/posts/{$post->id}/duplicate");
        
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('posts', [
            'content' => 'Original post',
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id
        ]);
    }

    /** @test */
    public function user_can_publish_post()
    {
        $this->actingAs($this->user);
        
        $post = Post::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'social_account_id' => $this->socialAccount->id,
            'status' => 'draft'
        ]);
        
        $response = $this->post("/posts/{$post->id}/publish");
        
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'status' => 'published'
        ]);
    }

    /** @test */
    public function user_can_view_post_analytics()
    {
        $this->actingAs($this->user);
        
        $post = Post::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'social_account_id' => $this->socialAccount->id,
            'external_id' => '12345'
        ]);
        
        $response = $this->get("/posts/{$post->id}/analytics");
        
        $response->assertStatus(200);
        $response->assertJsonStructure(['analytics', 'total_engagement']);
    }
} 