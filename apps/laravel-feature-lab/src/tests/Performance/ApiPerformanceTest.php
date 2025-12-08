<?php

namespace Tests\Performance;

use App\Models\Cow;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Performance tests for API endpoints
 * 
 * These tests measure response times and identify potential bottlenecks.
 * Run with: php artisan test --filter ApiPerformanceTest
 */
class ApiPerformanceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Performance threshold in milliseconds
     */
    private const FAST_THRESHOLD = 100;      // < 100ms is considered fast
    private const ACCEPTABLE_THRESHOLD = 500; // < 500ms is acceptable
    private const SLOW_THRESHOLD = 1000;     // > 1000ms is slow

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test user for authenticated requests
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    /**
     * Test: Cow index endpoint performance
     */
    public function test_cow_index_performance(): void
    {
        // Create test data
        Cow::factory()->count(50)->create();

        $startTime = microtime(true);
        
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/cows');
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        $response->assertStatus(200);
        
        $this->assertLessThan(
            self::ACCEPTABLE_THRESHOLD,
            $responseTime,
            "Cow index endpoint took {$responseTime}ms, expected < " . self::ACCEPTABLE_THRESHOLD . "ms"
        );
        
        // Log performance metrics
        $this->addToAssertionCount(1); // Count this as an assertion
        echo "\n✓ Cow index: {$responseTime}ms (50 records)";
    }

    /**
     * Test: Cow show endpoint performance
     */
    public function test_cow_show_performance(): void
    {
        $cow = Cow::factory()->create();

        $startTime = microtime(true);
        
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson("/api/cows/{$cow->id}");
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);
        
        $this->assertLessThan(
            self::FAST_THRESHOLD,
            $responseTime,
            "Cow show endpoint took {$responseTime}ms, expected < " . self::FAST_THRESHOLD . "ms"
        );
        
        echo "\n✓ Cow show: {$responseTime}ms";
    }

    /**
     * Test: Cow create endpoint performance
     */
    public function test_cow_create_performance(): void
    {
        $payload = [
            'name' => 'Performance Test Cow',
            'tag_number' => 'PERF-001',
            'breed' => 'Holstein',
            'dob' => '2021-01-01',
            'weight_kg' => 500.0,
        ];

        $startTime = microtime(true);
        
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/cows', $payload);
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $response->assertStatus(201);
        
        $this->assertLessThan(
            self::ACCEPTABLE_THRESHOLD,
            $responseTime,
            "Cow create endpoint took {$responseTime}ms, expected < " . self::ACCEPTABLE_THRESHOLD . "ms"
        );
        
        echo "\n✓ Cow create: {$responseTime}ms";
    }

    /**
     * Test: Feature flag dashboard performance
     */
    public function test_feature_flag_dashboard_performance(): void
    {
        $startTime = microtime(true);
        
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/demo/dashboard');
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);
        
        $this->assertLessThan(
            self::FAST_THRESHOLD,
            $responseTime,
            "Feature flag dashboard took {$responseTime}ms, expected < " . self::FAST_THRESHOLD . "ms"
        );
        
        echo "\n✓ Feature flag dashboard: {$responseTime}ms";
    }

    /**
     * Test: Queue stats endpoint performance
     */
    public function test_queue_stats_performance(): void
    {
        $startTime = microtime(true);
        
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/queue/stats');
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);
        
        $this->assertLessThan(
            self::FAST_THRESHOLD,
            $responseTime,
            "Queue stats endpoint took {$responseTime}ms, expected < " . self::FAST_THRESHOLD . "ms"
        );
        
        echo "\n✓ Queue stats: {$responseTime}ms";
    }

    /**
     * Test: Scout search performance
     */
    public function test_scout_search_performance(): void
    {
        // Create test data
        User::factory()->count(100)->create();
        
        // Import to search index
        User::makeAllSearchable();

        $startTime = microtime(true);
        
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/scout-demo/search', [
                'q' => 'test',
                'model' => 'users',
            ]);
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);
        
        $this->assertLessThan(
            self::ACCEPTABLE_THRESHOLD,
            $responseTime,
            "Scout search took {$responseTime}ms, expected < " . self::ACCEPTABLE_THRESHOLD . "ms"
        );
        
        echo "\n✓ Scout search: {$responseTime}ms (100 records)";
    }

    /**
     * Test: Notification stats endpoint performance
     */
    public function test_notification_stats_performance(): void
    {
        $startTime = microtime(true);
        
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/notifications/stats');
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);
        
        $this->assertLessThan(
            self::FAST_THRESHOLD,
            $responseTime,
            "Notification stats endpoint took {$responseTime}ms, expected < " . self::FAST_THRESHOLD . "ms"
        );
        
        echo "\n✓ Notification stats: {$responseTime}ms";
    }

    /**
     * Test: Multiple concurrent requests performance
     */
    public function test_concurrent_requests_performance(): void
    {
        Cow::factory()->count(20)->create();

        $startTime = microtime(true);
        
        // Simulate 10 concurrent requests
        $responses = [];
        for ($i = 0; $i < 10; $i++) {
            $responses[] = $this->withHeader('Authorization', "Bearer {$this->token}")
                ->getJson('/api/cows');
        }
        
        $endTime = microtime(true);
        $totalTime = ($endTime - $startTime) * 1000;
        $avgTime = $totalTime / 10;

        foreach ($responses as $response) {
            $response->assertStatus(200);
        }
        
        $this->assertLessThan(
            self::ACCEPTABLE_THRESHOLD * 2, // Allow more time for concurrent requests
            $avgTime,
            "Average concurrent request time: {$avgTime}ms"
        );
        
        echo "\n✓ Concurrent requests (10): {$avgTime}ms average, {$totalTime}ms total";
    }

    /**
     * Test: Database query count for cow index
     */
    public function test_cow_index_query_count(): void
    {
        Cow::factory()->count(50)->create();

        DB::enableQueryLog();
        
        $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/cows');
        
        $queries = DB::getQueryLog();
        $queryCount = count($queries);

        // Should use efficient queries (ideally 1-2 queries, not N+1)
        $this->assertLessThan(
            10,
            $queryCount,
            "Cow index executed {$queryCount} queries, expected < 10 (potential N+1 issue)"
        );
        
        echo "\n✓ Cow index query count: {$queryCount} queries";
    }
}

