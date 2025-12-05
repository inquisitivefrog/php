<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

/**
 * Task assigned notification
 * Demonstrates: Multi-channel notification (Email + Slack)
 */
class TaskAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public string $taskTitle,
        public string $assignedBy,
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
        $channels = ['mail'];
        
        // Add Slack if user has Slack channel configured
        if ($notifiable->slack_channel ?? false) {
            $channels[] = 'slack';
        }
        
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New Task Assigned: {$this->taskTitle}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("You have been assigned a new task: **{$this->taskTitle}**")
            ->line("Assigned by: {$this->assignedBy}")
            ->action('View Task', url("/tasks/{$this->taskTitle}"))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the Slack representation of the notification.
     */
    public function toSlack(object $notifiable): SlackMessage
    {
        return (new SlackMessage)
            ->content("New task assigned: {$this->taskTitle}")
            ->from('Laravel Feature Lab', ':robot_face:')
            ->attachment(function ($attachment) use ($notifiable) {
                $attachment->title("Task: {$this->taskTitle}")
                    ->fields([
                        'Assigned To' => $notifiable->name,
                        'Assigned By' => $this->assignedBy,
                    ])
                    ->action('View Task', url("/tasks/{$this->taskTitle}"));
            });
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'task_title' => $this->taskTitle,
            'assigned_by' => $this->assignedBy,
            'type' => 'task_assigned',
        ];
    }
}

