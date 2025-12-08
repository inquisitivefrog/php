<?php

namespace Tests\Unit\Notifications;

use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskAssignedNotificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: TaskAssignedNotification can be instantiated
     */
    public function test_task_assigned_notification_can_be_instantiated(): void
    {
        $notification = new TaskAssignedNotification('Task Name', 'Admin User');
        
        $this->assertEquals('Task Name', $notification->taskTitle);
        $this->assertEquals('Admin User', $notification->assignedBy);
    }

    /**
     * Test: TaskAssignedNotification via method returns mail and slack
     */
    public function test_task_assigned_notification_via_returns_mail_and_slack(): void
    {
        $user = User::factory()->create();
        $notification = new TaskAssignedNotification('Task Name', 'Admin User');
        
        $channels = $notification->via($user);
        
        $this->assertContains('mail', $channels);
        $this->assertContains('slack', $channels);
        $this->assertCount(2, $channels);
    }

    /**
     * Test: TaskAssignedNotification toMail returns MailMessage
     */
    public function test_task_assigned_notification_to_mail(): void
    {
        $user = User::factory()->create();
        $notification = new TaskAssignedNotification('Task Name', 'Admin User');
        
        $mailMessage = $notification->toMail($user);
        
        $this->assertInstanceOf(\Illuminate\Notifications\Messages\MailMessage::class, $mailMessage);
    }

    /**
     * Test: TaskAssignedNotification toSlack returns SlackMessage
     */
    public function test_task_assigned_notification_to_slack(): void
    {
        // Skip if SlackMessage class is not available
        if (!class_exists(\Illuminate\Notifications\Messages\SlackMessage::class)) {
            $this->markTestSkipped('SlackMessage class not available');
        }
        
        $user = User::factory()->create();
        $notification = new TaskAssignedNotification('Task Name', 'Admin User');
        
        $slackMessage = $notification->toSlack($user);
        
        $this->assertInstanceOf(\Illuminate\Notifications\Messages\SlackMessage::class, $slackMessage);
    }

    /**
     * Test: TaskAssignedNotification is queueable
     */
    public function test_task_assigned_notification_implements_should_queue(): void
    {
        $notification = new TaskAssignedNotification('Task Name', 'Admin User');
        
        $this->assertInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class, $notification);
    }
}

