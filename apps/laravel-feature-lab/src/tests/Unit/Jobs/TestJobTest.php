<?php

namespace Tests\Unit\Jobs;

use App\Jobs\TestJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class TestJobTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: TestJob can be instantiated
     */
    public function test_test_job_can_be_instantiated(): void
    {
        $job = new TestJob();
        $this->assertInstanceOf(TestJob::class, $job);
    }

    /**
     * Test: TestJob handles execution
     */
    public function test_test_job_handles_execution(): void
    {
        Log::shouldReceive('info')
            ->once()
            ->with('TestJob ran successfully!');
        
        $job = new TestJob();
        $job->handle();
    }

    /**
     * Test: TestJob is queueable
     */
    public function test_test_job_implements_should_queue(): void
    {
        $job = new TestJob();
        
        $this->assertInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class, $job);
    }
}

