<?php

namespace Tests\Performance;

use App\Models\Cow;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Database performance tests
 * 
 * Tests database query performance, N+1 query detection, and bulk operations.
 */
class DatabasePerformanceTest extends TestCase
{
    use RefreshDatabase;

    private const FAST_THRESHOLD = 50;      // < 50ms for DB operations
    private const ACCEPTABLE_THRESHOLD = 200; // < 200ms acceptable

    /**
     * Test: Bulk insert performance
     */
    public function test_bulk_insert_performance(): void
    {
        $count = 100;
        
        $startTime = microtime(true);
        
        Cow::factory()->count($count)->create();
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;
        $perRecord = $responseTime / $count;

        $this->assertLessThan(
            self::ACCEPTABLE_THRESHOLD * 10, // Allow more time for bulk operations
            $responseTime,
            "Bulk insert of {$count} records took {$responseTime}ms"
        );
        
        echo "\n✓ Bulk insert ({$count} records): {$responseTime}ms ({$perRecord}ms per record)";
    }

    /**
     * Test: Query optimization - avoiding unnecessary queries
     */
    public function test_query_optimization_performance(): void
    {
        // Create test data
        Cow::factory()->count(100)->create();

        // Test inefficient approach (multiple queries)
        DB::enableQueryLog();
        $startTime = microtime(true);
        
        $cows = Cow::all();
        $totalWeight = 0;
        foreach ($cows as $cow) {
            // Accessing attributes individually (simulating inefficient pattern)
            $totalWeight += $cow->weight_kg;
        }
        
        $endTime = microtime(true);
        $inefficientTime = ($endTime - $startTime) * 1000;
        $inefficientQueries = count(DB::getQueryLog());

        // Test efficient approach (single query with aggregation)
        DB::flushQueryLog();
        $startTime = microtime(true);
        
        $totalWeightEfficient = Cow::sum('weight_kg');
        
        $endTime = microtime(true);
        $efficientTime = ($endTime - $startTime) * 1000;
        $efficientQueries = count(DB::getQueryLog());

        // Efficient approach should be faster (using database aggregation)
        $this->assertLessThan(
            $inefficientTime,
            $efficientTime,
            "Efficient query should be faster. Inefficient: {$inefficientTime}ms, Efficient: {$efficientTime}ms"
        );
        
        // Use approximate comparison for floating point values
        $this->assertEqualsWithDelta($totalWeight, $totalWeightEfficient, 0.01);
        
        $improvement = (($inefficientTime - $efficientTime) / $inefficientTime) * 100;
        
        echo "\n✓ Inefficient approach: {$inefficientQueries} queries, {$inefficientTime}ms";
        echo "\n✓ Efficient approach: {$efficientQueries} queries, {$efficientTime}ms";
        echo "\n✓ Performance improvement: " . number_format($improvement, 2) . "%";
    }

    /**
     * Test: Pagination performance
     */
    public function test_pagination_performance(): void
    {
        Cow::factory()->count(200)->create();

        $startTime = microtime(true);
        
        $cows = Cow::paginate(20);
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(
            self::ACCEPTABLE_THRESHOLD,
            $responseTime,
            "Pagination took {$responseTime}ms"
        );
        
        $this->assertEquals(20, $cows->count());
        
        echo "\n✓ Pagination (20 per page, 200 total): {$responseTime}ms";
    }

    /**
     * Test: Complex query performance
     */
    public function test_complex_query_performance(): void
    {
        Cow::factory()->count(100)->create([
            'weight_kg' => fn() => rand(300, 800),
            'breed' => fn() => collect(['Holstein', 'Jersey', 'Angus', 'Hereford'])->random(),
        ]);

        $startTime = microtime(true);
        
        $results = Cow::where('weight_kg', '>', 500)
            ->whereIn('breed', ['Holstein', 'Jersey'])
            ->orderBy('weight_kg', 'desc')
            ->limit(10)
            ->get();
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(
            self::FAST_THRESHOLD,
            $responseTime,
            "Complex query took {$responseTime}ms"
        );
        
        echo "\n✓ Complex query (filters + order + limit): {$responseTime}ms";
    }

    /**
     * Test: Transaction performance
     */
    public function test_transaction_performance(): void
    {
        $startTime = microtime(true);
        
        DB::transaction(function () {
            for ($i = 0; $i < 50; $i++) {
                Cow::factory()->create();
            }
        });
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(
            self::ACCEPTABLE_THRESHOLD * 5,
            $responseTime,
            "Transaction with 50 inserts took {$responseTime}ms"
        );
        
        echo "\n✓ Transaction (50 inserts): {$responseTime}ms";
    }

    /**
     * Test: Count query performance
     */
    public function test_count_query_performance(): void
    {
        Cow::factory()->count(500)->create();

        $startTime = microtime(true);
        
        $count = Cow::count();
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(
            self::FAST_THRESHOLD,
            $responseTime,
            "Count query took {$responseTime}ms"
        );
        
        $this->assertEquals(500, $count);
        
        echo "\n✓ Count query (500 records): {$responseTime}ms";
    }

    /**
     * Test: Update performance
     */
    public function test_bulk_update_performance(): void
    {
        $cows = Cow::factory()->count(100)->create();

        $startTime = microtime(true);
        
        Cow::whereIn('id', $cows->pluck('id'))
            ->update(['weight_kg' => 600]);
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(
            self::ACCEPTABLE_THRESHOLD,
            $responseTime,
            "Bulk update took {$responseTime}ms"
        );
        
        echo "\n✓ Bulk update (100 records): {$responseTime}ms";
    }
}

