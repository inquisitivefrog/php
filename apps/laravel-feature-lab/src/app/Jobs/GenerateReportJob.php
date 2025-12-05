<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Example job: Generate report
 * Demonstrates: Long-running job, unique jobs, middleware
 */
class GenerateReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 300;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 1;

    /**
     * The unique ID of the job.
     */
    public string $uniqueId;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $reportType,
        public array $parameters = [],
    ) {
        $this->uniqueId = "report-{$reportType}-" . md5(serialize($parameters));
        $this->onQueue('reports');
    }

    /**
     * The unique ID of the job.
     */
    public function uniqueId(): string
    {
        return $this->uniqueId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Generating report: {$this->reportType}");
        
        // Simulate report generation
        sleep(3);
        
        Log::info("Report generated: {$this->reportType}");
    }
}

