<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Comment;

class NewCommentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Comment $comment)
    {
    }

    /**
     * Get the notification channels.
     */
    public function via($notifiable): array
    {
        return ['mail', 'slack'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Comment Posted')
            ->line('A new comment was added by ' . $this->comment->user->name . '.')
            ->line('Post ID: ' . $this->comment->post_id)
            ->line('Comment: ' . $this->comment->content)
            ->action('View Post', url('/posts/' . $this->comment->post_id))
            ->line('Thank you for using our application!');
    }

    /**
     * Send a Slack message via incoming webhook.  Instead of using
     * the slack-notification-channel package (not installed), we
     * perform a simple POST request to the webhook URL.  Ensure
     * SLACK_WEBHOOK_URL is set in your .env file.
     */
    public function toSlack($notifiable)
    {
        $webhookUrl = config('services.slack.webhook_url') ?? env('SLACK_WEBHOOK_URL');
        if (! $webhookUrl) {
            return;
        }
        $message = [
            'text' => sprintf(
                "New comment by %s on post %s: %s",
                $this->comment->user->name,
                $this->comment->post_id,
                $this->comment->content
            ),
        ];
        try {
            Http::post($webhookUrl, $message);
        } catch (\Throwable $e) {
            \Log::warning('Failed to send Slack notification: ' . $e->getMessage());
        }
    }
}