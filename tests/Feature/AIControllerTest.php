<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class AIControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $tenant;
    protected $post;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create tenant and user
        $this->tenant = Tenant::factory()->create();
        $this->user = User::factory()->create();
        $this->tenant->users()->attach($this->user, ['role' => 'owner']);
        
        // Create a post
        $this->post = Post::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'content' => 'Test post content'
        ]);
        
        // Set current tenant
        app()->instance('currentTenant', $this->tenant);
        
        // Fake storage
        Storage::fake('public');
    }

    /** @test */
    public function user_can_generate_captions()
    {
        $this->actingAs($this->user);
        
        $requestData = [
            'content' => 'Test post content',
            'platform' => 'facebook',
            'tone' => 'professional'
        ];
        
        // Mock OpenAI API response
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'Generated caption for Facebook'
                        ]
                    ]
                ]
            ], 200)
        ]);
        
        $response = $this->post('/api/ai/generate-captions', $requestData);
        
        $response->assertStatus(200);
        $response->assertJsonStructure(['captions']);
    }

    /** @test */
    public function caption_generation_requires_content()
    {
        $this->actingAs($this->user);
        
        $requestData = [
            'platform' => 'facebook',
            'tone' => 'professional'
        ];
        
        $response = $this->post('/api/ai/generate-captions', $requestData);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['content']);
    }

    /** @test */
    public function user_can_generate_alt_text()
    {
        $this->actingAs($this->user);
        
        $file = UploadedFile::fake()->image('test.jpg', 100, 100);
        
        $requestData = [
            'image' => $file,
            'context' => 'Social media post'
        ];
        
        // Mock OpenAI API response
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'A colorful image showing various elements'
                        ]
                    ]
                ]
            ], 200)
        ]);
        
        $response = $this->post('/api/ai/generate-alt-text', $requestData);
        
        $response->assertStatus(200);
        $response->assertJsonStructure(['alt_text']);
    }

    /** @test */
    public function alt_text_generation_requires_image()
    {
        $this->actingAs($this->user);
        
        $requestData = [
            'context' => 'Social media post'
        ];
        
        $response = $this->post('/api/ai/generate-alt-text', $requestData);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['image']);
    }

    /** @test */
    public function user_can_get_content_suggestions()
    {
        $this->actingAs($this->user);
        
        $requestData = [
            'topic' => 'Technology',
            'platform' => 'linkedin',
            'tone' => 'professional',
            'length' => 'short'
        ];
        
        // Mock OpenAI API response
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'Here are some content suggestions for LinkedIn'
                        ]
                    ]
                ]
            ], 200)
        ]);
        
        $response = $this->post('/api/ai/content-suggestions', $requestData);
        
        $response->assertStatus(200);
        $response->assertJsonStructure(['suggestions']);
    }

    /** @test */
    public function content_suggestions_require_topic()
    {
        $this->actingAs($this->user);
        
        $requestData = [
            'platform' => 'linkedin',
            'tone' => 'professional'
        ];
        
        $response = $this->post('/api/ai/content-suggestions', $requestData);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['topic']);
    }

    /** @test */
    public function ai_endpoints_require_authentication()
    {
        $requestData = [
            'content' => 'Test content',
            'platform' => 'facebook'
        ];
        
        $response = $this->post('/api/ai/generate-captions', $requestData);
        
        $response->assertStatus(401);
    }

    /** @test */
    public function ai_endpoints_require_tenant_context()
    {
        $this->actingAs($this->user);
        
        // Remove tenant context
        app()->forgetInstance('currentTenant');
        
        $requestData = [
            'content' => 'Test content',
            'platform' => 'facebook'
        ];
        
        $response = $this->post('/api/ai/generate-captions', $requestData);
        
        $response->assertStatus(500);
    }

    /** @test */
    public function caption_generation_supports_different_platforms()
    {
        $this->actingAs($this->user);
        
        $platforms = ['facebook', 'twitter', 'linkedin', 'instagram'];
        
        foreach ($platforms as $platform) {
            $requestData = [
                'content' => 'Test content for ' . $platform,
                'platform' => $platform,
                'tone' => 'casual'
            ];
            
            // Mock OpenAI API response
            Http::fake([
                'api.openai.com/*' => Http::response([
                    'choices' => [
                        [
                            'message' => [
                                'content' => "Generated caption for {$platform}"
                            ]
                        ]
                    ]
                ], 200)
            ]);
            
            $response = $this->post('/api/ai/generate-captions', $requestData);
            
            $response->assertStatus(200);
        }
    }

    /** @test */
    public function caption_generation_supports_different_tones()
    {
        $this->actingAs($this->user);
        
        $tones = ['professional', 'casual', 'friendly', 'formal'];
        
        foreach ($tones as $tone) {
            $requestData = [
                'content' => 'Test content',
                'platform' => 'facebook',
                'tone' => $tone
            ];
            
            // Mock OpenAI API response
            Http::fake([
                'api.openai.com/*' => Http::response([
                    'choices' => [
                        [
                            'message' => [
                                'content' => "Generated caption with {$tone} tone"
                            ]
                        ]
                    ]
                ], 200)
            ]);
            
            $response = $this->post('/api/ai/generate-captions', $requestData);
            
            $response->assertStatus(200);
        }
    }

    /** @test */
    public function alt_text_generation_handles_different_image_types()
    {
        $this->actingAs($this->user);
        
        $imageTypes = ['jpg', 'png', 'gif'];
        
        foreach ($imageTypes as $type) {
            $file = UploadedFile::fake()->image("test.{$type}", 100, 100);
            
            $requestData = [
                'image' => $file,
                'context' => 'Social media post'
            ];
            
            // Mock OpenAI API response
            Http::fake([
                'api.openai.com/*' => Http::response([
                    'choices' => [
                        [
                            'message' => [
                                'content' => "Alt text for {$type} image"
                            ]
                        ]
                    ]
                ], 200)
            ]);
            
            $response = $this->post('/api/ai/generate-alt-text', $requestData);
            
            $response->assertStatus(200);
        }
    }

    /** @test */
    public function content_suggestions_support_different_lengths()
    {
        $this->actingAs($this->user);
        
        $lengths = ['short', 'medium', 'long'];
        
        foreach ($lengths as $length) {
            $requestData = [
                'topic' => 'Technology',
                'platform' => 'linkedin',
                'tone' => 'professional',
                'length' => $length
            ];
            
            // Mock OpenAI API response
            Http::fake([
                'api.openai.com/*' => Http::response([
                    'choices' => [
                        [
                            'message' => [
                                'content' => "Content suggestions with {$length} length"
                            ]
                        ]
                    ]
                ], 200)
            ]);
            
            $response = $this->post('/api/ai/content-suggestions', $requestData);
            
            $response->assertStatus(200);
        }
    }

    /** @test */
    public function ai_endpoints_handle_api_errors_gracefully()
    {
        $this->actingAs($this->user);
        
        $requestData = [
            'content' => 'Test content',
            'platform' => 'facebook'
        ];
        
        // Mock OpenAI API error response
        Http::fake([
            'api.openai.com/*' => Http::response([
                'error' => [
                    'message' => 'API rate limit exceeded'
                ]
            ], 429)
        ]);
        
        $response = $this->post('/api/ai/generate-captions', $requestData);
        
        $response->assertStatus(500);
        $response->assertJson(['error' => 'Failed to generate captions']);
    }

    /** @test */
    public function ai_endpoints_validate_platform_values()
    {
        $this->actingAs($this->user);
        
        $requestData = [
            'content' => 'Test content',
            'platform' => 'invalid_platform'
        ];
        
        $response = $this->post('/api/ai/generate-captions', $requestData);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['platform']);
    }

    /** @test */
    public function ai_endpoints_validate_tone_values()
    {
        $this->actingAs($this->user);
        
        $requestData = [
            'content' => 'Test content',
            'platform' => 'facebook',
            'tone' => 'invalid_tone'
        ];
        
        $response = $this->post('/api/ai/generate-captions', $requestData);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['tone']);
    }
} 