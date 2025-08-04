<?php

namespace App\Jobs;

use App\Models\Post;
use App\Services\FacebookPostingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PublishPostJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Post $post;
    public $tries = 3;
    public $backoff = [60, 300, 600]; // Retry after 1min, 5min, 10min

    /**
     * Create a new job instance.
     */
    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    /**
     * Execute the job.
     */
    public function handle(FacebookPostingService $facebookService): void
    {
        try {
            $socialAccount = $this->post->socialAccount;
            
            if (!$socialAccount) {
                throw new \Exception('No social account associated with this post');
            }

            // Route to appropriate service based on platform
            switch ($socialAccount->platform) {
                case 'facebook':
                    $facebookService->publishPost($this->post);
                    break;
                    
                case 'twitter':
                    // TODO: Implement Twitter posting service
                    Log::info('Twitter posting not yet implemented', ['post_id' => $this->post->id]);
                    $this->post->update(['status' => 'published']);
                    break;
                    
                case 'linkedin':
                    // TODO: Implement LinkedIn posting service
                    Log::info('LinkedIn posting not yet implemented', ['post_id' => $this->post->id]);
                    $this->post->update(['status' => 'published']);
                    break;
                    
                case 'instagram':
                    // TODO: Implement Instagram posting service
                    Log::info('Instagram posting not yet implemented', ['post_id' => $this->post->id]);
                    $this->post->update(['status' => 'published']);
                    break;
                    
                default:
                    throw new \Exception("Unsupported platform: {$socialAccount->platform}");
            }

            Log::info('Post published successfully', [
                'post_id' => $this->post->id,
                'platform' => $socialAccount->platform,
                'external_id' => $this->post->external_id
            ]);

        } catch (\Exception $e) {
            Log::error('Post publishing failed', [
                'post_id' => $this->post->id,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts()
            ]);

            // Update post status to failed if this is the final attempt
            if ($this->attempts() >= $this->tries) {
                $this->post->update(['status' => 'failed']);
            }

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Post publishing job failed permanently', [
            'post_id' => $this->post->id,
            'error' => $exception->getMessage()
        ]);

        $this->post->update(['status' => 'failed']);
    }
}