<?php

namespace App\Http\Controllers;

use App\Jobs\TestJob;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

/**
 * Telescope demo controller demonstrating various activities that Telescope monitors
 * 
 * Telescope automatically captures:
 * - Requests (HTTP requests/responses)
 * - Queries (database queries)
 * - Models (Eloquent model operations)
 * - Events (event dispatching)
 * - Jobs (queue jobs)
 * - Mail (emails sent)
 * - Notifications (notifications sent)
 * - Cache (cache operations)
 * - Commands (artisan commands)
 * - Scheduled Tasks
 * - Views (view rendering)
 * - Exceptions (exceptions thrown)
 * - Logs (log entries)
 * - Dumps (dd/dump calls)
 */
class TelescopeDemoController extends Controller
{
    /**
     * Demonstrate database queries
     * Telescope captures: Queries, Models
     */
    public function databaseQueries(): JsonResponse
    {
        // Simple query
        $users = User::take(5)->get();
        
        // Query with relationships
        $userWithRelations = User::with('subscriptions')->first();
        
        // Raw query
        $count = DB::table('users')->count();
        
        // Complex query
        $recentUsers = User::where('created_at', '>', now()->subDays(30))
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'Database queries executed',
            'users_count' => $users->count(),
            'total_users' => $count,
            'recent_users' => $recentUsers->count(),
            'note' => 'Check Telescope dashboard to see all queries captured',
        ]);
    }

    /**
     * Demonstrate cache operations
     * Telescope captures: Cache
     */
    public function cacheOperations(): JsonResponse
    {
        // Cache put
        Cache::put('telescope_demo_key', 'telescope_demo_value', 3600);
        
        // Cache get
        $value = Cache::get('telescope_demo_key');
        
        // Cache remember
        $remembered = Cache::remember('telescope_demo_remember', 3600, function () {
            return 'remembered_value';
        });
        
        // Cache forget
        Cache::forget('telescope_demo_key');

        return response()->json([
            'message' => 'Cache operations executed',
            'cached_value' => $value,
            'remembered_value' => $remembered,
            'note' => 'Check Telescope dashboard to see cache operations',
        ]);
    }

    /**
     * Demonstrate job dispatching
     * Telescope captures: Jobs
     */
    public function dispatchJob(): JsonResponse
    {
        TestJob::dispatch();

        return response()->json([
            'message' => 'Job dispatched',
            'job' => TestJob::class,
            'note' => 'Check Telescope dashboard to see job entry',
        ]);
    }

    /**
     * Demonstrate logging
     * Telescope captures: Logs
     */
    public function logging(): JsonResponse
    {
        Log::info('Telescope demo: Info log');
        Log::warning('Telescope demo: Warning log');
        Log::error('Telescope demo: Error log');
        Log::debug('Telescope demo: Debug log');

        return response()->json([
            'message' => 'Logs written',
            'note' => 'Check Telescope dashboard to see log entries',
        ]);
    }

    /**
     * Demonstrate exceptions
     * Telescope captures: Exceptions
     */
    public function throwException(): JsonResponse
    {
        try {
            throw new \Exception('Telescope demo exception');
        } catch (\Exception $e) {
            Log::error('Exception caught: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Exception thrown and caught',
                'exception' => $e->getMessage(),
                'note' => 'Check Telescope dashboard to see exception entry',
            ], 500);
        }
    }

    /**
     * Demonstrate model operations
     * Telescope captures: Models
     */
    public function modelOperations(): JsonResponse
    {
        // Create
        $user = User::factory()->create([
            'name' => 'Telescope Demo User',
            'email' => 'telescope-demo-' . uniqid() . '@example.com',
        ]);
        
        // Update
        $user->update(['name' => 'Updated Telescope Demo User']);
        
        // Read
        $found = User::find($user->id);
        
        // Delete
        $user->delete();

        return response()->json([
            'message' => 'Model operations executed',
            'user_id' => $user->id,
            'note' => 'Check Telescope dashboard to see model operations',
        ]);
    }

    /**
     * Demonstrate events
     * Telescope captures: Events
     */
    public function dispatchEvent(): JsonResponse
    {
        event(new \App\Events\TelescopeDemoEvent('Telescope demo event data'));

        return response()->json([
            'message' => 'Event dispatched',
            'note' => 'Check Telescope dashboard to see event entry',
        ]);
    }

    /**
     * Demonstrate multiple operations
     * Telescope captures: All entry types
     */
    public function multipleOperations(): JsonResponse
    {
        // Database query
        $users = User::count();
        
        // Cache operation
        Cache::put('telescope_multi_demo', 'value', 60);
        
        // Log entry
        Log::info('Telescope multi-operation demo');
        
        // Job dispatch
        TestJob::dispatch();

        return response()->json([
            'message' => 'Multiple operations executed',
            'users_count' => $users,
            'note' => 'Check Telescope dashboard to see all entry types',
        ]);
    }

    /**
     * Demonstrate slow query
     * Telescope captures: Slow queries
     */
    public function slowQuery(): JsonResponse
    {
        // Simulate a slow query (using a simple delay instead of pg_sleep for compatibility)
        usleep(100000); // 0.1 seconds
        
        $users = User::all();

        return response()->json([
            'message' => 'Slow query executed',
            'users_count' => $users->count(),
            'note' => 'Check Telescope dashboard to see slow query warning',
        ]);
    }

    /**
     * Demonstrate N+1 query problem
     * Telescope captures: N+1 queries
     */
    public function nPlusOneQuery(): JsonResponse
    {
        // This will trigger N+1 queries (Telescope will detect this)
        $users = User::take(10)->get();
        
        $data = [];
        foreach ($users as $user) {
            // Accessing relationship without eager loading causes N+1
            $data[] = [
                'id' => $user->id,
                'name' => $user->name,
                // Uncomment to see N+1 detection:
                // 'subscriptions' => $user->subscriptions,
            ];
        }

        return response()->json([
            'message' => 'Query executed (may show N+1 in Telescope)',
            'users_processed' => count($data),
            'note' => 'Check Telescope dashboard for query analysis',
        ]);
    }
}

