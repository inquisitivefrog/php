<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

/**
 * System alert notification
 * Demonstrates: Error/alert notifications to multiple channels
 */
class SystemAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public string $alertType,
        public string $message,
        public ?string $details = null,
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'slack'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mailMessage = (new MailMessage)
            ->subject("System Alert: {$this->alertType}")
            ->line($this->message);

        if ($this->details) {
            $mailMessage->line("**Details:** {$this->details}");
        }

        if ($this->alertType === 'error') {
            $mailMessage->error();
        } elseif ($this->alertType === 'warning') {
            $mailMessage->warning();
        } else {
            $mailMessage->success();
        }

        return $mailMessage;
    }

    /**
     * Get the Slack representation of the notification.
     */
    public function toSlack(object $notifiable): SlackMessage
    {
        $slackMessage = (new SlackMessage)
            ->content("System Alert: {$this->message}")
            ->from('Laravel Feature Lab', ':warning:');

        if ($this->alertType === 'error') {
            $slackMessage->error();
        } elseif ($this->alertType === 'warning') {
            $slackMessage->warning();
        } else {
            $slackMessage->success();
        }

        if ($this->details) {
            $slackMessage->attachment(function ($attachment) {
                $attachment->title('Details')
                    ->content($this->details);
            });
        }

        return $slackMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'alert_type' => $this->alertType,
            'message' => $this->message,
            'details' => $this->details,
            'type' => 'system_alert',
        ];
    }
}

