<?php

namespace Tests\Performance;

use App\Models\User;
use App\Notifications\WelcomeEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Notification performance tests
 * 
 * Tests notification sending and queuing performance.
 */
class NotificationPerformanceTest extends TestCase
{
    use RefreshDatabase;

    private const FAST_THRESHOLD = 10;      // < 10ms for notification
    private const ACCEPTABLE_THRESHOLD = 50; // < 50ms acceptable

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    /**
     * Test: Single notification send performance
     */
    public function test_single_notification_performance(): void
    {
        $user = User::factory()->create();
        $iterations = 100;

        $startTime = microtime(true);
        
        for ($i = 0; $i < $iterations; $i++) {
            $user->notify(new WelcomeEmailNotification("User {$i}"));
        }
        
        $endTime = microtime(true);
        $totalTime = ($endTime - $startTime) * 1000;
        $avgTime = $totalTime / $iterations;

        $this->assertLessThan(
            self::ACCEPTABLE_THRESHOLD,
            $avgTime,
            "Average notification send time: {$avgTime}ms"
        );
        
        Notification::assertSentTimes(WelcomeEmailNotification::class, $iterations);
        
        echo "\n✓ Single notification send ({$iterations} notifications): {$avgTime}ms average";
    }

    /**
     * Test: Bulk notification send performance
     */
    public function test_bulk_notification_performance(): void
    {
        $users = User::factory()->count(100)->create();

        $startTime = microtime(true);
        
        Notification::send($users, new WelcomeEmailNotification('Bulk Notification'));
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;
        $perUser = $responseTime / $users->count();

        $this->assertLessThan(
            self::ACCEPTABLE_THRESHOLD * 2,
            $responseTime,
            "Bulk notification send took {$responseTime}ms"
        );
        
        Notification::assertSentTimes(WelcomeEmailNotification::class, $users->count());
        
        echo "\n✓ Bulk notification send (100 users): {$responseTime}ms ({$perUser}ms per user)";
    }

    /**
     * Test: Queued notification dispatch performance
     */
    public function test_queued_notification_performance(): void
    {
        $user = User::factory()->create();
        $iterations = 50;

        $startTime = microtime(true);
        
        for ($i = 0; $i < $iterations; $i++) {
            $user->notify(new WelcomeEmailNotification("Queued User {$i}"));
        }
        
        $endTime = microtime(true);
        $totalTime = ($endTime - $startTime) * 1000;
        $avgTime = $totalTime / $iterations;

        $this->assertLessThan(
            self::ACCEPTABLE_THRESHOLD,
            $avgTime,
            "Average queued notification dispatch time: {$avgTime}ms"
        );
        
        echo "\n✓ Queued notification dispatch ({$iterations} notifications): {$avgTime}ms average";
    }

    /**
     * Test: Multi-channel notification performance
     */
    public function test_multi_channel_notification_performance(): void
    {
        $user = User::factory()->create();
        $user->slack_channel = '#notifications';
        $iterations = 50;

        $startTime = microtime(true);
        
        for ($i = 0; $i < $iterations; $i++) {
            $user->notify(new \App\Notifications\TaskAssignedNotification("Task {$i}", "Admin"));
        }
        
        $endTime = microtime(true);
        $totalTime = ($endTime - $startTime) * 1000;
        $avgTime = $totalTime / $iterations;

        $this->assertLessThan(
            self::ACCEPTABLE_THRESHOLD,
            $avgTime,
            "Average multi-channel notification time: {$avgTime}ms"
        );
        
        echo "\n✓ Multi-channel notification ({$iterations} notifications): {$avgTime}ms average";
    }
}

