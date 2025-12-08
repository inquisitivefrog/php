<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\OrderConfirmationNotification;
use App\Notifications\PasswordResetSmsNotification;
use App\Notifications\SystemAlertNotification;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\WelcomeEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Comprehensive tests demonstrating Laravel Notification capabilities
 * 
 * Cost considerations:
 * - Email: FREE (using Mailpit for testing, Mail::fake() for unit tests)
 * - Slack: FREE (webhook URL, Notification::fake() for testing)
 * - SMS: Can cost money (services like Twilio/Vonage), but we use Notification::fake() for testing
 * 
 * All external services are mocked in tests to avoid costs.
 */
class NotificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Send welcome email notification
     * Demonstrates: Basic email notification
     */
    public function test_send_welcome_email_notification(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $user->notify(new WelcomeEmailNotification($user->name));

        Notification::assertSentTo(
            $user,
            WelcomeEmailNotification::class,
            function ($notification, $channels) {
                return in_array('mail', $channels);
            }
        );
    }

    /**
     * Test: Send task assigned notification (multi-channel)
     * Demonstrates: Email + Slack notifications
     */
    public function test_send_task_assigned_notification(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $user->notify(new TaskAssignedNotification('Complete project', 'John Doe'));

        Notification::assertSentTo(
            $user,
            TaskAssignedNotification::class,
            function ($notification, $channels) {
                return in_array('mail', $channels);
            }
        );
    }

    /**
     * Test: Send password reset SMS notification
     * Demonstrates: SMS notification (mocked)
     */
    public function test_send_password_reset_sms_notification(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $user->notify(new PasswordResetSmsNotification('123456'));

        Notification::assertSentTo(
            $user,
            PasswordResetSmsNotification::class,
            function ($notification, $channels) {
                return in_array('vonage', $channels);
            }
        );
    }

    /**
     * Test: Send order confirmation notification
     * Demonstrates: Rich email with multiple channels
     */
    public function test_send_order_confirmation_notification(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $user->notify(new OrderConfirmationNotification(
            'ORD-12345',
            99.99,
            ['Item 1', 'Item 2', 'Item 3']
        ));

        Notification::assertSentTo(
            $user,
            OrderConfirmationNotification::class,
            function ($notification, $channels) {
                return in_array('mail', $channels) && in_array('slack', $channels);
            }
        );
    }

    /**
     * Test: Send system alert notification
     * Demonstrates: Alert notifications to multiple channels
     */
    public function test_send_system_alert_notification(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $user->notify(new SystemAlertNotification(
            'error',
            'System error occurred',
            'Database connection failed'
        ));

        Notification::assertSentTo(
            $user,
            SystemAlertNotification::class,
            function ($notification, $channels) {
                return in_array('mail', $channels) && in_array('slack', $channels);
            }
        );
    }

    /**
     * Test: Send notification to multiple users
     * Demonstrates: Broadcasting notifications
     */
    public function test_send_notification_to_multiple_users(): void
    {
        Notification::fake();

        $users = User::factory()->count(5)->create();

        Notification::send($users, new SystemAlertNotification(
            'info',
            'System maintenance scheduled'
        ));

        Notification::assertSentTimes(SystemAlertNotification::class, 5);

        foreach ($users as $user) {
            Notification::assertSentTo(
                $user,
                SystemAlertNotification::class
            );
        }
    }

    /**
     * Test: Notification via API endpoint
     * Demonstrates: API-triggered notifications
     */
    public function test_send_welcome_email_via_api(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/notifications/welcome');

        $response->assertStatus(200);
        $response->assertJsonStructure(['message', 'user', 'channel']);

        Notification::assertSentTo($user, WelcomeEmailNotification::class);
    }

    /**
     * Test: Task assigned notification via API
     * Demonstrates: API-triggered multi-channel notifications
     */
    public function test_send_task_assigned_via_api(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/notifications/task-assigned', [
                'task_title' => 'Complete project',
                'assigned_by' => 'John Doe',
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['message', 'task_title', 'channels']);

        Notification::assertSentTo($user, TaskAssignedNotification::class);
    }

    /**
     * Test: Password reset SMS via API
     * Demonstrates: API-triggered SMS notifications
     */
    public function test_send_password_reset_sms_via_api(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/notifications/password-reset-sms', [
                'reset_code' => '123456',
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['message', 'reset_code', 'channel']);

        Notification::assertSentTo($user, PasswordResetSmsNotification::class);
    }

    /**
     * Test: Order confirmation via API
     * Demonstrates: API-triggered rich notifications
     */
    public function test_send_order_confirmation_via_api(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/notifications/order-confirmation', [
                'order_id' => 'ORD-12345',
                'amount' => 99.99,
                'items' => ['Item 1', 'Item 2'],
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['message', 'order_id', 'channels']);

        Notification::assertSentTo($user, OrderConfirmationNotification::class);
    }

    /**
     * Test: System alert via API
     * Demonstrates: API-triggered alert notifications
     */
    public function test_send_system_alert_via_api(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/notifications/system-alert', [
                'alert_type' => 'error',
                'message' => 'System error occurred',
                'details' => 'Database connection failed',
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['message', 'alert_type', 'channels']);

        Notification::assertSentTo($user, SystemAlertNotification::class);
    }

    /**
     * Test: Broadcast notification via API
     * Demonstrates: API-triggered broadcast notifications
     */
    public function test_send_broadcast_notification_via_api(): void
    {
        Notification::fake();

        User::factory()->count(3)->create();
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/notifications/broadcast', [
                'message' => 'System maintenance scheduled',
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['message', 'recipients_count', 'channels']);

        // Should send to multiple users
        Notification::assertSentTimes(SystemAlertNotification::class, 4);
    }

    /**
     * Test: Notification with queued delivery
     * Demonstrates: Queued notifications
     */
    public function test_queued_notification(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        // WelcomeEmailNotification implements ShouldQueue
        $user->notify(new WelcomeEmailNotification($user->name));

        // Notification should be queued
        Notification::assertSentTo($user, WelcomeEmailNotification::class);
    }

    /**
     * Test: Notification channels configuration
     * Demonstrates: Dynamic channel selection
     */
    public function test_notification_channels_configuration(): void
    {
        $user = User::factory()->create();

        // TaskAssignedNotification checks for slack_channel
        $notification = new TaskAssignedNotification('Task', 'Admin');
        $channels = $notification->via($user);

        // Should include mail, but not slack if user doesn't have slack_channel
        $this->assertContains('mail', $channels);
    }

    /**
     * Test: Notification to anonymous notifiable
     * Demonstrates: Sending to non-model recipients
     */
    public function test_notification_to_anonymous_notifiable(): void
    {
        Notification::fake();

        $notifiable = new AnonymousNotifiable();
        $notifiable->route('mail', 'test@example.com');

        Notification::send($notifiable, new WelcomeEmailNotification('Test User'));

        Notification::assertSentTo(
            $notifiable,
            WelcomeEmailNotification::class
        );
    }

    /**
     * Test: Notification with custom data
     * Demonstrates: Custom notification data
     */
    public function test_notification_with_custom_data(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $notification = new OrderConfirmationNotification('ORD-123', 99.99, ['Item 1']);

        $user->notify($notification);

        $arrayData = $notification->toArray($user);

        $this->assertEquals('ORD-123', $arrayData['order_id']);
        $this->assertEquals(99.99, $arrayData['amount']);
        $this->assertEquals(['Item 1'], $arrayData['items']);
        $this->assertEquals('order_confirmation', $arrayData['type']);
    }

    /**
     * Test: Notification statistics endpoint
     * Demonstrates: Notification configuration info
     */
    public function test_notification_stats_endpoint(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/notifications/stats');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'mail_driver',
            'mail_host',
            'slack_configured',
            'mailpit_url',
        ]);
    }

    /**
     * Test: Notification validation
     * Demonstrates: Request validation for notifications
     */
    public function test_notification_validation(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        // Missing required fields
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/notifications/task-assigned', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['task_title', 'assigned_by']);
    }

    /**
     * Test: Multiple notification types
     * Demonstrates: Different notification types
     */
    public function test_multiple_notification_types(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        // Send different types of notifications
        $user->notify(new WelcomeEmailNotification($user->name));
        $user->notify(new TaskAssignedNotification('Task 1', 'Admin'));
        $user->notify(new SystemAlertNotification('info', 'System update'));

        Notification::assertSentTo($user, WelcomeEmailNotification::class);
        Notification::assertSentTo($user, TaskAssignedNotification::class);
        Notification::assertSentTo($user, SystemAlertNotification::class);
    }

    /**
     * Test: Notification with different alert types
     * Demonstrates: Error, warning, and info alerts
     */
    public function test_notification_alert_types(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        // Test different alert types
        $user->notify(new SystemAlertNotification('error', 'Error message'));
        $user->notify(new SystemAlertNotification('warning', 'Warning message'));
        $user->notify(new SystemAlertNotification('info', 'Info message'));

        Notification::assertSentTimes(SystemAlertNotification::class, 3);
    }
}