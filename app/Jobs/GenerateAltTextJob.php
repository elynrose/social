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

class GenerateAltTextJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 600]; // Retry after 1, 5, 10 minutes
    public $timeout = 120;

    protected $postId;

    public function __construct($postId)
    {
        $this->postId = $postId;
    }

    public function handle(AIService $aiService)
    {
        try {
            $post = Post::findOrFail($this->postId);
            
            if (!$post->media_path) {
                Log::info('No media found for post', ['post_id' => $this->postId]);
                return;
            }

            $altText = $aiService->generateAltText($post->media_path, $post->content);
            
            if ($altText) {
                $post->update(['alt_text' => $altText]);
                Log::info('Alt text generated successfully', [
                    'post_id' => $this->postId,
                    'alt_text' => $altText,
                ]);
            } else {
                Log::warning('Failed to generate alt text', ['post_id' => $this->postId]);
            }
        } catch (\Exception $e) {
            Log::error('Alt text generation job failed', [
                'post_id' => $this->postId,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error('Alt text generation job failed permanently', [
            'post_id' => $this->postId,
            'error' => $exception->getMessage(),
        ]);
    }
}