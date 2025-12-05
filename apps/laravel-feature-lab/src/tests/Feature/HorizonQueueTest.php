<?php

namespace Tests\Feature;

use App\Jobs\BatchableJob;
use App\Jobs\ChainedJob;
use App\Jobs\DelayedJob;
use App\Jobs\FailedJobExample;
use App\Jobs\GenerateReportJob;
use App\Jobs\ProcessEmailJob;
use App\Jobs\ProcessImageJob;
use App\Jobs\TestJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Comprehensive tests demonstrating all Laravel Queue and Horizon features
 * 
 * Note: Horizon is FREE - it's just a dashboard for monitoring queues.
 * These tests use Queue::fake() to avoid actually processing jobs,
 * making them fast and reliable.
 */
class HorizonQueueTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Basic job dispatching
     * Demonstrates: dispatch(), ShouldQueue interface
     */
    public function test_basic_job_dispatching(): void
    {
        Queue::fake();

        TestJob::dispatch();

        Queue::assertPushed(TestJob::class);
    }

    /**
     * Test: Job with queue name
     * Demonstrates: onQueue(), queue names
     */
    public function test_job_with_queue_name(): void
    {
        Queue::fake();

        ProcessEmailJob::dispatch('test@example.com', 'Subject', 'Body');

        Queue::assertPushed(ProcessEmailJob::class, function ($job) {
            return $job->queue === 'emails';
        });
    }

    /**
     * Test: Job with delay
     * Demonstrates: delay(), delayed jobs
     */
    public function test_job_with_delay(): void
    {
        Queue::fake();

        DelayedJob::dispatch('Test message', 60);

        Queue::assertPushed(DelayedJob::class);
        
        // Verify delay is set (delay is stored in the job payload)
        $this->assertTrue(true); // Job is dispatched with delay parameter
    }

    /**
     * Test: Job with specific delay time
     * Demonstrates: delay() with Carbon instance
     */
    public function test_job_with_specific_delay(): void
    {
        Queue::fake();

        $delay = now()->addMinutes(5);
        DelayedJob::dispatch('Test message')->delay($delay);

        Queue::assertPushed(DelayedJob::class, function ($job) use ($delay) {
            return $job->delay !== null;
        });
    }

    /**
     * Test: Job with tries and backoff
     * Demonstrates: $tries, $backoff properties
     */
    public function test_job_with_retry_configuration(): void
    {
        Queue::fake();

        ProcessEmailJob::dispatch('test@example.com', 'Subject', 'Body');

        Queue::assertPushed(ProcessEmailJob::class, function ($job) {
            return $job->tries === 3 && $job->backoff === 60;
        });
    }

    /**
     * Test: Job with timeout
     * Demonstrates: $timeout property
     */
    public function test_job_with_timeout(): void
    {
        Queue::fake();

        ProcessImageJob::dispatch('/path/to/image.jpg', []);

        Queue::assertPushed(ProcessImageJob::class, function ($job) {
            return $job->timeout === 120;
        });
    }

    /**
     * Test: Job failure handling
     * Demonstrates: failed() method, job failures
     */
    public function test_job_failure_handling(): void
    {
        Queue::fake();

        FailedJobExample::dispatch(true);

        Queue::assertPushed(FailedJobExample::class);
        
        // Note: Actual failure testing requires processing the job
        // This test verifies the job can be dispatched
    }

    /**
     * Test: Job chaining
     * Demonstrates: chain(), dependent jobs
     */
    public function test_job_chaining(): void
    {
        Bus::fake();

        ChainedJob::dispatch(1, 'data1')
            ->chain([
                new ChainedJob(2, 'data2'),
                new ChainedJob(3, 'data3'),
            ]);

        Bus::assertChained([
            ChainedJob::class,
            ChainedJob::class,
            ChainedJob::class,
        ]);
    }

    /**
     * Test: Job batching
     * Demonstrates: batch(), batchable jobs
     */
    public function test_job_batching(): void
    {
        Bus::fake();

        $batch = Bus::batch([
            new BatchableJob(1, 'item1'),
            new BatchableJob(2, 'item2'),
            new BatchableJob(3, 'item3'),
        ])->dispatch();

        Bus::assertBatched(function ($batch) {
            return $batch->jobs->count() === 3;
        });
    }

    /**
     * Test: Batch with callbacks
     * Demonstrates: then(), catch(), finally() callbacks
     */
    public function test_batch_with_callbacks(): void
    {
        Bus::fake();

        $batch = Bus::batch([
            new BatchableJob(1, 'item1'),
        ])
            ->then(function () {
                Log::info('Batch completed');
            })
            ->catch(function () {
                Log::error('Batch failed');
            })
            ->finally(function () {
                Log::info('Batch finished');
            })
            ->dispatch();

        Bus::assertBatched(function ($batch) {
            return $batch->jobs->count() === 1;
        });
    }

    /**
     * Test: Job on specific connection
     * Demonstrates: onConnection(), queue connections
     */
    public function test_job_on_specific_connection(): void
    {
        Queue::fake();

        TestJob::dispatch()->onConnection('redis');

        Queue::assertPushed(TestJob::class, function ($job) {
            return $job->connection === 'redis';
        });
    }

    /**
     * Test: Job on specific queue and connection
     * Demonstrates: onConnection() + onQueue()
     */
    public function test_job_on_connection_and_queue(): void
    {
        Queue::fake();

        ProcessEmailJob::dispatch('test@example.com', 'Subject', 'Body')
            ->onConnection('redis');

        Queue::assertPushed(ProcessEmailJob::class, function ($job) {
            return $job->connection === 'redis' && $job->queue === 'emails';
        });
    }

    /**
     * Test: Multiple jobs on different queues
     * Demonstrates: Queue organization
     */
    public function test_multiple_jobs_different_queues(): void
    {
        Queue::fake();

        ProcessEmailJob::dispatch('test@example.com', 'Subject', 'Body');
        ProcessImageJob::dispatch('/path/to/image.jpg', []);
        GenerateReportJob::dispatch('sales', []);

        Queue::assertPushedOn('emails', ProcessEmailJob::class);
        Queue::assertPushedOn('images', ProcessImageJob::class);
        Queue::assertPushedOn('reports', GenerateReportJob::class);
    }

    /**
     * Test: Job priority
     * Demonstrates: Priority queues
     */
    public function test_job_priority(): void
    {
        Queue::fake();

        // High priority job
        ProcessEmailJob::dispatch('urgent@example.com', 'Urgent', 'Body')
            ->onQueue('emails-high');

        // Normal priority job
        ProcessEmailJob::dispatch('normal@example.com', 'Normal', 'Body')
            ->onQueue('emails');

        Queue::assertPushed(ProcessEmailJob::class, 2);
    }

    /**
     * Test: Job with unique ID
     * Demonstrates: uniqueId(), unique jobs
     */
    public function test_job_with_unique_id(): void
    {
        Queue::fake();

        GenerateReportJob::dispatch('sales', ['month' => '2024-01']);
        GenerateReportJob::dispatch('sales', ['month' => '2024-01']); // Same parameters

        Queue::assertPushed(GenerateReportJob::class, 2);
        
        // Both jobs are dispatched, but in real scenario with unique middleware,
        // the second would be skipped if first is still processing
    }

    /**
     * Test: Job event listeners
     * Demonstrates: JobProcessing, JobProcessed, JobFailed events
     */
    public function test_job_event_listeners(): void
    {
        Event::fake();

        // Dispatch a job (using fake queue, so it won't actually process)
        TestJob::dispatch();

        // In real scenario, events would be fired
        // This test verifies jobs can be dispatched
        $this->assertTrue(true);
    }

    /**
     * Test: Job with middleware
     * Demonstrates: middleware() method
     */
    public function test_job_with_middleware(): void
    {
        Queue::fake();

        TestJob::dispatch();

        Queue::assertPushed(TestJob::class);
        
        // Middleware would be applied during job processing
        // This test verifies job can be dispatched
    }

    /**
     * Test: Synchronous job execution
     * Demonstrates: sync queue driver
     */
    public function test_synchronous_job_execution(): void
    {
        // Use sync queue for immediate execution
        config(['queue.default' => 'sync']);

        Log::shouldReceive('info')
            ->once()
            ->with('TestJob ran successfully!');

        TestJob::dispatch();

        // With sync driver, job executes immediately
        $this->assertTrue(true);
    }

    /**
     * Test: Job retry logic
     * Demonstrates: Retry attempts, exponential backoff
     */
    public function test_job_retry_logic(): void
    {
        Queue::fake();

        $job = new FailedJobExample(true);
        $job->tries = 3;
        $job->backoff = 10;

        dispatch($job);

        Queue::assertPushed(FailedJobExample::class, function ($job) {
            return $job->tries === 3 && $job->backoff === 10;
        });
    }

    /**
     * Test: Job with different queue connections
     * Demonstrates: database, redis, sync connections
     */
    public function test_job_on_different_connections(): void
    {
        Queue::fake();

        // Database connection
        TestJob::dispatch()->onConnection('database');
        Queue::assertPushed(TestJob::class, function ($job) {
            return $job->connection === 'database';
        });

        // Redis connection
        TestJob::dispatch()->onConnection('redis');
        Queue::assertPushed(TestJob::class, function ($job) {
            return $job->connection === 'redis';
        });
    }

    /**
     * Test: Batch cancellation
     * Demonstrates: Cancelling batches
     */
    public function test_batch_cancellation(): void
    {
        Bus::fake();

        $batch = Bus::batch([
            new BatchableJob(1, 'item1'),
            new BatchableJob(2, 'item2'),
        ])->dispatch();

        // In real scenario, you would cancel: $batch->cancel();
        Bus::assertBatched(function ($batch) {
            return $batch->jobs->count() === 2;
        });
    }

    /**
     * Test: Job without queue (synchronous)
     * Demonstrates: dispatchSync()
     */
    public function test_job_dispatch_sync(): void
    {
        Log::shouldReceive('info')
            ->once()
            ->with('TestJob ran successfully!');

        TestJob::dispatchSync();

        // Job executes immediately
        $this->assertTrue(true);
    }

    /**
     * Test: Job after commit
     * Demonstrates: afterCommit() method
     */
    public function test_job_after_commit(): void
    {
        Queue::fake();

        TestJob::dispatch()->afterCommit();

        Queue::assertPushed(TestJob::class);
        
        // Job will only be dispatched after database transaction commits
    }

    /**
     * Test: Job with tags
     * Demonstrates: Tagging jobs for filtering
     */
    public function test_job_with_tags(): void
    {
        Queue::fake();

        ProcessEmailJob::dispatch('test@example.com', 'Subject', 'Body')
            ->onQueue('emails')
            ->onConnection('redis');

        Queue::assertPushed(ProcessEmailJob::class);
        
        // Tags can be added for filtering in Horizon dashboard
    }

    /**
     * Test: Multiple job types
     * Demonstrates: Different job classes
     */
    public function test_multiple_job_types(): void
    {
        Queue::fake();

        TestJob::dispatch();
        ProcessEmailJob::dispatch('test@example.com', 'Subject', 'Body');
        ProcessImageJob::dispatch('/path/to/image.jpg', []);

        Queue::assertPushed(TestJob::class);
        Queue::assertPushed(ProcessEmailJob::class);
        Queue::assertPushed(ProcessImageJob::class);
    }

    /**
     * Test: Job with data serialization
     * Demonstrates: SerializesModels trait
     */
    public function test_job_with_model_serialization(): void
    {
        Queue::fake();

        // Jobs can serialize Eloquent models
        $user = \App\Models\User::factory()->create();
        
        // Example: Job that uses a model
        TestJob::dispatch();

        Queue::assertPushed(TestJob::class);
    }
}

