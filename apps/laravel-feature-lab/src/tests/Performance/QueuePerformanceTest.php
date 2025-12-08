<?php

namespace Tests\Performance;

use App\Jobs\BatchableJob;
use App\Jobs\TestJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Queue performance tests
 * 
 * Tests queue job dispatch and processing performance.
 */
class QueuePerformanceTest extends TestCase
{
    use RefreshDatabase;

    private const FAST_THRESHOLD = 10;      // < 10ms for dispatch
    private const ACCEPTABLE_THRESHOLD = 50; // < 50ms acceptable

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    /**
     * Test: Job dispatch performance
     */
    public function test_job_dispatch_performance(): void
    {
        $iterations = 100;
        
        $startTime = microtime(true);
        
        for ($i = 0; $i < $iterations; $i++) {
            TestJob::dispatch("Test message {$i}");
        }
        
        $endTime = microtime(true);
        $totalTime = ($endTime - $startTime) * 1000;
        $avgTime = $totalTime / $iterations;

        $this->assertLessThan(
            self::ACCEPTABLE_THRESHOLD,
            $avgTime,
            "Average job dispatch time: {$avgTime}ms"
        );
        
        Queue::assertPushed(TestJob::class, $iterations);
        
        echo "\n✓ Job dispatch ({$iterations} jobs): {$avgTime}ms average, {$totalTime}ms total";
    }

    /**
     * Test: Batch job dispatch performance
     */
    public function test_batch_job_dispatch_performance(): void
    {
        $jobCount = 50;
        
        $startTime = microtime(true);
        
        $jobs = [];
        for ($i = 0; $i < $jobCount; $i++) {
            $jobs[] = new BatchableJob($i, "Batch job {$i}");
        }
        
        \Illuminate\Support\Facades\Bus::batch($jobs)->dispatch();
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(
            self::ACCEPTABLE_THRESHOLD * 2,
            $responseTime,
            "Batch dispatch took {$responseTime}ms"
        );
        
        echo "\n✓ Batch job dispatch ({$jobCount} jobs): {$responseTime}ms";
    }

    /**
     * Test: Chained job dispatch performance
     */
    public function test_chained_job_dispatch_performance(): void
    {
        $chainLength = 10;
        
        $startTime = microtime(true);
        
        $jobs = [];
        for ($i = 0; $i < $chainLength; $i++) {
            $jobs[] = new TestJob("Chain job {$i}");
        }
        
        \Illuminate\Support\Facades\Bus::chain($jobs)->dispatch();
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(
            self::ACCEPTABLE_THRESHOLD,
            $responseTime,
            "Chained job dispatch took {$responseTime}ms"
        );
        
        echo "\n✓ Chained job dispatch ({$chainLength} jobs): {$responseTime}ms";
    }

    /**
     * Test: Multiple queue connection performance
     */
    public function test_multiple_queue_connections_performance(): void
    {
        $iterations = 20;
        
        $startTime = microtime(true);
        
        for ($i = 0; $i < $iterations; $i++) {
            TestJob::dispatch("Job {$i}")->onConnection('redis');
            TestJob::dispatch("Job {$i}")->onQueue('high');
        }
        
        $endTime = microtime(true);
        $totalTime = ($endTime - $startTime) * 1000;
        $avgTime = $totalTime / ($iterations * 2);

        $this->assertLessThan(
            self::ACCEPTABLE_THRESHOLD,
            $avgTime,
            "Average multi-queue dispatch time: {$avgTime}ms"
        );
        
        echo "\n✓ Multiple queue connections ({$iterations} jobs each): {$avgTime}ms average";
    }
}

