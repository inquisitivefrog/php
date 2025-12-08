<?php

namespace App\Http\Controllers;

use App\Jobs\BatchableJob;
use App\Jobs\ChainedJob;
use App\Jobs\DelayedJob;
use App\Jobs\FailedJobExample;
use App\Jobs\GenerateReportJob;
use App\Jobs\ProcessEmailJob;
use App\Jobs\ProcessImageJob;
use App\Jobs\TestJob;
use Illuminate\Bus\Batch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Log;

/**
 * Queue demo controller demonstrating various queue features
 */
class QueueDemoController extends Controller
{
    /**
     * Dispatch a basic test job
     */
    public function dispatchTestJob(): JsonResponse
    {
        TestJob::dispatch();

        return response()->json([
            'message' => 'Test job dispatched successfully',
            'job' => TestJob::class,
        ]);
    }

    /**
     * Dispatch an email job
     */
    public function dispatchEmailJob(Request $request): JsonResponse
    {
        $request->validate([
            'to' => 'required|email',
            'subject' => 'required|string',
            'body' => 'required|string',
        ]);

        ProcessEmailJob::dispatch(
            $request->input('to'),
            $request->input('subject'),
            $request->input('body')
        );

        return response()->json([
            'message' => 'Email job dispatched successfully',
            'queue' => 'emails',
        ]);
    }

    /**
     * Dispatch a delayed job
     */
    public function dispatchDelayedJob(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string',
            'delay_seconds' => 'integer|min:0|max:3600',
        ]);

        $delay = $request->input('delay_seconds', 0);

        DelayedJob::dispatch($request->input('message'), $delay)
            ->delay(now()->addSeconds($delay));

        return response()->json([
            'message' => 'Delayed job dispatched successfully',
            'delay_seconds' => $delay,
        ]);
    }

    /**
     * Dispatch a chained job
     */
    public function dispatchChainedJob(): JsonResponse
    {
        $chain = Bus::chain([
            new ChainedJob(1, 'Step 1 data'),
            new ChainedJob(2, 'Step 2 data'),
            new ChainedJob(3, 'Step 3 data'),
        ])->dispatch();

        return response()->json([
            'message' => 'Chained jobs dispatched successfully',
            'steps' => 3,
        ]);
    }

    /**
     * Dispatch a batch of jobs
     */
    public function dispatchBatchJob(Request $request): JsonResponse
    {
        $request->validate([
            'items' => 'required|array|min:1|max:10',
            'items.*' => 'required|string',
        ]);

        $jobs = collect($request->input('items'))->map(function ($item, $index) {
            return new BatchableJob($index + 1, $item);
        })->toArray();

        $batch = Bus::batch($jobs)
            ->then(function (Batch $batch) {
                Log::info("Batch {$batch->id} completed successfully");
            })
            ->catch(function (Batch $batch, \Throwable $e) {
                Log::error("Batch {$batch->id} failed: {$e->getMessage()}");
            })
            ->finally(function (Batch $batch) {
                Log::info("Batch {$batch->id} finished");
            })
            ->dispatch();

        return response()->json([
            'message' => 'Batch jobs dispatched successfully',
            'batch_id' => $batch->id,
            'job_count' => count($jobs),
        ]);
    }

    /**
     * Dispatch a job that will fail (for testing)
     */
    public function dispatchFailedJob(): JsonResponse
    {
        FailedJobExample::dispatch(true);

        return response()->json([
            'message' => 'Failed job dispatched (will fail after retries)',
            'tries' => 3,
            'backoff' => 10,
        ]);
    }

    /**
     * Get queue statistics
     */
    public function queueStats(): JsonResponse
    {
        // Note: Real queue stats require Horizon or queue monitoring
        // This is a simplified version
        return response()->json([
            'message' => 'Queue statistics',
            'note' => 'Use Horizon dashboard at /horizon for detailed stats',
            'queues' => [
                'default' => 'General purpose queue',
                'emails' => 'Email processing queue',
                'images' => 'Image processing queue',
                'reports' => 'Report generation queue',
            ],
        ]);
    }
}

