<?php

namespace Tests\Unit\Notifications;

use App\Models\User;
use App\Notifications\WelcomeEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Messages\MailMessage;
use Tests\TestCase;

class WelcomeEmailNotificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: WelcomeEmailNotification can be instantiated
     */
    public function test_welcome_email_notification_can_be_instantiated(): void
    {
        $notification = new WelcomeEmailNotification('John Doe');
        
        $this->assertEquals('John Doe', $notification->userName);
    }

    /**
     * Test: WelcomeEmailNotification via method returns mail channel
     */
    public function test_welcome_email_notification_via_returns_mail(): void
    {
        $user = User::factory()->create();
        $notification = new WelcomeEmailNotification('John Doe');
        
        $channels = $notification->via($user);
        
        $this->assertContains('mail', $channels);
        $this->assertCount(1, $channels);
    }

    /**
     * Test: WelcomeEmailNotification toMail returns MailMessage
     */
    public function test_welcome_email_notification_to_mail_returns_mail_message(): void
    {
        $user = User::factory()->create();
        $notification = new WelcomeEmailNotification('John Doe');
        
        $mailMessage = $notification->toMail($user);
        
        $this->assertInstanceOf(MailMessage::class, $mailMessage);
    }

    /**
     * Test: WelcomeEmailNotification toArray returns correct structure
     */
    public function test_welcome_email_notification_to_array(): void
    {
        $user = User::factory()->create();
        $notification = new WelcomeEmailNotification('John Doe');
        
        $array = $notification->toArray($user);
        
        $this->assertEquals('John Doe', $array['user_name']);
        $this->assertEquals('welcome', $array['type']);
    }

    /**
     * Test: WelcomeEmailNotification is queueable
     */
    public function test_welcome_email_notification_implements_should_queue(): void
    {
        $notification = new WelcomeEmailNotification('John Doe');
        
        $this->assertInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class, $notification);
    }
}



