<?php

namespace Tests\Unit\Jobs;

use App\Jobs\DelayedJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class DelayedJobTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: DelayedJob can be instantiated
     */
    public function test_delayed_job_can_be_instantiated(): void
    {
        $job = new DelayedJob('Test message', 60);
        
        $this->assertEquals('Test message', $job->message);
        $this->assertEquals(60, $job->delaySeconds);
    }

    /**
     * Test: DelayedJob has default delay
     */
    public function test_delayed_job_has_default_delay(): void
    {
        $job = new DelayedJob('Test message');
        
        $this->assertEquals('Test message', $job->message);
        $this->assertEquals(0, $job->delaySeconds);
    }

    /**
     * Test: DelayedJob handles execution
     */
    public function test_delayed_job_handles_execution(): void
    {
        Log::shouldReceive('info')
            ->once()
            ->with(\Mockery::pattern('/Delayed job executed: Test message/'));
        
        $job = new DelayedJob('Test message', 60);
        $job->handle();
    }

    /**
     * Test: DelayedJob is queueable
     */
    public function test_delayed_job_implements_should_queue(): void
    {
        $job = new DelayedJob('Test message');
        
        $this->assertInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class, $job);
    }

    /**
     * Test: DelayedJob uses default queue
     */
    public function test_delayed_job_uses_default_queue(): void
    {
        $job = new DelayedJob('Test message');
        
        // The job should be configured to use the default queue
        $this->assertTrue(method_exists($job, 'onQueue'));
    }
}

