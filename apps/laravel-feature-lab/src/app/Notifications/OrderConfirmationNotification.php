<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

/**
 * Order confirmation notification
 * Demonstrates: Rich email with attachments, multi-channel
 */
class OrderConfirmationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public string $orderId,
        public float $amount,
        public array $items,
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
        $message = (new MailMessage)
            ->subject("Order Confirmation #{$this->orderId}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("Thank you for your order! Your order #{$this->orderId} has been confirmed.")
            ->line("**Order Total:** $" . number_format($this->amount, 2))
            ->line("**Items:**")
            ->line(implode("\n", array_map(fn($item) => "- {$item}", $this->items)))
            ->action('View Order', url("/orders/{$this->orderId}"))
            ->line('We will send you another email when your order ships.');

        return $message;
    }

    /**
     * Get the Slack representation of the notification.
     */
    public function toSlack(object $notifiable): SlackMessage
    {
        return (new SlackMessage)
            ->success()
            ->content("New order received: #{$this->orderId}")
            ->from('Laravel Feature Lab', ':moneybag:')
            ->attachment(function ($attachment) use ($notifiable) {
                $attachment->title("Order #{$this->orderId}")
                    ->fields([
                        'Customer' => $notifiable->name,
                        'Amount' => '$' . number_format($this->amount, 2),
                        'Items' => count($this->items),
                    ])
                    ->action('View Order', url("/orders/{$this->orderId}"));
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
            'order_id' => $this->orderId,
            'amount' => $this->amount,
            'items' => $this->items,
            'type' => 'order_confirmation',
        ];
    }
}

