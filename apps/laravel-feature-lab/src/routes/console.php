<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Scheduled Tasks
 * 
 * These tasks run automatically via the scheduler container.
 * View scheduled tasks: php artisan schedule:list
 * Run scheduler manually: php artisan schedule:work
 */

// Hourly: Health check and cache warming
Schedule::call(function () {
    Log::info('Hourly health check: Application is running');
    
    // Warm up frequently used cache
    Cache::remember('health_check', 3600, function () {
        return [
            'status' => 'healthy',
            'timestamp' => now()->toIso8601String(),
            'database' => DB::connection()->getPdo() ? 'connected' : 'disconnected',
        ];
    });
})->hourly()->name('health-check');

// Daily: Cleanup old Telescope entries (keep last 7 days)
Schedule::command('telescope:prune --hours 168')
    ->daily()
    ->at('02:00')
    ->name('telescope-cleanup')
    ->onOneServer();

// Daily: Generate daily activity report
Schedule::call(function () {
    $userCount = DB::table('users')->count();
    $cowCount = DB::table('cows')->count();
    
    Log::info('Daily activity report', [
        'users' => $userCount,
        'cows' => $cowCount,
        'date' => now()->toDateString(),
    ]);
    
    // Store in cache for dashboard
    Cache::put('daily_report_' . now()->toDateString(), [
        'users' => $userCount,
        'cows' => $cowCount,
        'generated_at' => now()->toIso8601String(),
    ], now()->addDays(30));
})->daily()->at('03:00')->name('daily-report');

// Weekly: Database optimization and maintenance
Schedule::call(function () {
    Log::info('Weekly maintenance: Starting database optimization');
    
    // Example: Clean up expired cache entries
    // In a real app, you might run VACUUM ANALYZE on PostgreSQL
    // or optimize tables in MySQL
    
    Cache::flush(); // Optional: clear cache weekly (adjust based on needs)
    
    Log::info('Weekly maintenance: Completed');
})->weekly()->sundays()->at('04:00')->name('weekly-maintenance');

// Every 5 minutes: Check queue health
Schedule::call(function () {
    $pendingJobs = DB::table('jobs')->count();
    $failedJobs = DB::table('failed_jobs')->count();
    
    if ($pendingJobs > 1000 || $failedJobs > 100) {
        Log::warning('Queue health check: High job counts', [
            'pending' => $pendingJobs,
            'failed' => $failedJobs,
        ]);
    } else {
        Log::debug('Queue health check: Normal', [
            'pending' => $pendingJobs,
            'failed' => $failedJobs,
        ]);
    }
})->everyFiveMinutes()->name('queue-health-check');

// Daily: Send scheduled notifications (example)
Schedule::call(function () {
    // Example: Send daily digest to users
    // In a real app, you might send daily summaries, reminders, etc.
    Log::info('Daily notification digest: Ready to send');
    
    // This is a placeholder - in a real app you'd dispatch jobs or send notifications
    // Example: User::where('wants_daily_digest', true)->chunk(100, function ($users) {
    //     foreach ($users as $user) {
    //         $user->notify(new DailyDigestNotification());
    //     }
    // });
})->daily()->at('09:00')->name('daily-notifications');

// Hourly: Cache statistics update
Schedule::call(function () {
    try {
        // Test cache connection
        $testKey = 'cache_health_check_' . time();
        Cache::put($testKey, 'ok', 60);
        $cacheWorking = Cache::get($testKey) === 'ok';
        Cache::forget($testKey);
        
        $cacheStats = [
            'cache_working' => $cacheWorking,
            'driver' => config('cache.default'),
            'timestamp' => now()->toIso8601String(),
        ];
        
        Cache::put('cache_stats', $cacheStats, 3600);
    } catch (\Exception $e) {
        Log::warning('Cache stats update failed', ['error' => $e->getMessage()]);
    }
})->hourly()->name('cache-stats');
