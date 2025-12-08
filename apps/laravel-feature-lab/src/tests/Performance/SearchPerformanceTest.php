<?php

namespace Tests\Performance;

use App\Models\Cow;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Search performance tests (Scout/Meilisearch)
 * 
 * Tests search query performance and indexing performance.
 */
class SearchPerformanceTest extends TestCase
{
    use RefreshDatabase;

    private const FAST_THRESHOLD = 50;      // < 50ms for search
    private const ACCEPTABLE_THRESHOLD = 200; // < 200ms acceptable

    /**
     * Test: Basic search performance
     */
    public function test_basic_search_performance(): void
    {
        // Create and index test data
        User::factory()->count(100)->create();
        User::makeAllSearchable();

        $startTime = microtime(true);
        
        $results = User::search('test')->get();
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(
            self::ACCEPTABLE_THRESHOLD,
            $responseTime,
            "Basic search took {$responseTime}ms"
        );
        
        echo "\n✓ Basic search (100 records): {$responseTime}ms";
    }

    /**
     * Test: Paginated search performance
     */
    public function test_paginated_search_performance(): void
    {
        User::factory()->count(200)->create();
        User::makeAllSearchable();

        $startTime = microtime(true);
        
        $results = User::search('test')->paginate(20);
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(
            self::ACCEPTABLE_THRESHOLD,
            $responseTime,
            "Paginated search took {$responseTime}ms"
        );
        
        echo "\n✓ Paginated search (200 records, 20 per page): {$responseTime}ms";
    }

    /**
     * Test: Filtered search performance
     */
    public function test_filtered_search_performance(): void
    {
        User::factory()->count(150)->create();
        User::makeAllSearchable();

        $startTime = microtime(true);
        
        $results = User::search('test')
            ->where('email', 'like', '%example%')
            ->get();
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(
            self::ACCEPTABLE_THRESHOLD,
            $responseTime,
            "Filtered search took {$responseTime}ms"
        );
        
        echo "\n✓ Filtered search (150 records): {$responseTime}ms";
    }

    /**
     * Test: Bulk indexing performance
     */
    public function test_bulk_indexing_performance(): void
    {
        $count = 500;
        User::factory()->count($count)->create();

        $startTime = microtime(true);
        
        User::makeAllSearchable();
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;
        $perRecord = $responseTime / $count;

        $this->assertLessThan(
            self::ACCEPTABLE_THRESHOLD * 20, // Allow more time for indexing
            $responseTime,
            "Bulk indexing took {$responseTime}ms"
        );
        
        echo "\n✓ Bulk indexing ({$count} records): {$responseTime}ms ({$perRecord}ms per record)";
    }

    /**
     * Test: Multiple model search performance
     */
    public function test_multiple_model_search_performance(): void
    {
        User::factory()->count(100)->create();
        Cow::factory()->count(100)->create();
        
        User::makeAllSearchable();
        Cow::makeAllSearchable();

        $startTime = microtime(true);
        
        $userResults = User::search('test')->get();
        $cowResults = Cow::search('cow')->get();
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(
            self::ACCEPTABLE_THRESHOLD * 2,
            $responseTime,
            "Multiple model search took {$responseTime}ms"
        );
        
        echo "\n✓ Multiple model search (2 models, 100 records each): {$responseTime}ms";
    }

    /**
     * Test: Search with ordering performance
     */
    public function test_ordered_search_performance(): void
    {
        User::factory()->count(150)->create();
        User::makeAllSearchable();

        $startTime = microtime(true);
        
        $results = User::search('test')
            ->orderBy('name', 'asc')
            ->get();
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(
            self::ACCEPTABLE_THRESHOLD,
            $responseTime,
            "Ordered search took {$responseTime}ms"
        );
        
        echo "\n✓ Ordered search (150 records): {$responseTime}ms";
    }
}


