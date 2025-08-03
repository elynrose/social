<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Post;
use App\Services\AIService;
use Illuminate\Support\Facades\Log;

class GenerateCaptionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 600]; // Retry after 1, 5, 10 minutes
    public $timeout = 120;

    protected $postId;
    protected $platform;
    protected $tone;

    public function __construct($postId, $platform = 'general', $tone = 'professional')
    {
        $this->postId = $postId;
        $this->platform = $platform;
        $this->tone = $tone;
    }

    public function handle(AIService $aiService)
    {
        try {
            $post = Post::findOrFail($this->postId);
            
            $captions = $aiService->generateCaptions($post->content, $this->platform, $this->tone);
            
            if (!empty($captions)) {
                // Store captions as JSON in the captions_path field
                $captionsData = [
                    'captions' => $captions,
                    'platform' => $this->platform,
                    'tone' => $this->tone,
                    'generated_at' => now()->toISOString(),
                ];
                
                $post->update(['captions_path' => json_encode($captionsData)]);
                
                Log::info('Captions generated successfully', [
                    'post_id' => $this->postId,
                    'platform' => $this->platform,
                    'captions_count' => count($captions),
                ]);
            } else {
                Log::warning('Failed to generate captions', ['post_id' => $this->postId]);
            }
        } catch (\Exception $e) {
            Log::error('Caption generation job failed', [
                'post_id' => $this->postId,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error('Caption generation job failed permanently', [
            'post_id' => $this->postId,
            'error' => $exception->getMessage(),
        ]);
    }
}