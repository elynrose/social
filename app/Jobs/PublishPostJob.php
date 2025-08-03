<?php

namespace App\Jobs;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * PublishPostJob is responsible for publishing a scheduled post to the
 * appropriate social network.  The actual API integration should be
 * implemented here.  For now, it simply marks the post as published.
 */
class PublishPostJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Post $post;

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
    public function handle(): void
    {
        // TODO: Integrate with the social platform's API using the
        // associated SocialAccount's access token.  For example, you
        // could dispatch HTTP requests to the Facebook, Twitter or
        // LinkedIn API here.
        // Example pseudo-code:
        // $platform = $this->post->socialAccount->platform;
        // $token    = decrypt($this->post->socialAccount->access_token);
        // switch ($platform) {
        //     case 'facebook':
        //         FacebookApi::publish($token, $this->post->content, $this->post->media_path);
        //         break;
        //     ...
        // }

        // For this scaffold we just update the post status to published.
        $this->post->status = 'published';
        $this->post->save();
    }
}