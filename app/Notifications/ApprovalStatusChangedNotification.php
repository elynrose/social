<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Approval;

class ApprovalStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Approval $approval)
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
            ->subject('Approval Status Updated')
            ->line('The approval status for a post has been updated by ' . $this->approval->user->name . '.')
            ->line('Post ID: ' . $this->approval->post_id)
            ->line('New Status: ' . ucfirst($this->approval->status))
            ->line('Comments: ' . ($this->approval->comments ?? ''))
            ->action('View Approval', url('/approvals'))
            ->line('Thank you for using our application!');
    }

    /**
     * Send a Slack message via incoming webhook.
     */
    public function toSlack($notifiable)
    {
        $webhookUrl = config('services.slack.webhook_url') ?? env('SLACK_WEBHOOK_URL');
        if (! $webhookUrl) {
            return;
        }
        $message = [
            'text' => sprintf(
                "Approval status for post %s changed to %s by %s",
                $this->approval->post_id,
                $this->approval->status,
                $this->approval->user->name
            ),
        ];
        try {
            Http::post($webhookUrl, $message);
        } catch (\Throwable $e) {
            \Log::warning('Failed to send Slack notification: ' . $e->getMessage());
        }
    }
}