<?php

namespace Tests\Performance;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Tests\TestCase;

/**
 * Feature flag performance tests
 * 
 * Tests feature flag evaluation performance.
 */
class FeatureFlagPerformanceTest extends TestCase
{
    use RefreshDatabase;

    private const FAST_THRESHOLD = 5;      // < 5ms for feature flag check
    private const ACCEPTABLE_THRESHOLD = 20; // < 20ms acceptable

    /**
     * Test: Single feature flag check performance
     */
    public function test_single_feature_flag_check_performance(): void
    {
        $user = User::factory()->create();
        $iterations = 1000;

        $startTime = microtime(true);
        
        for ($i = 0; $i < $iterations; $i++) {
            Feature::for($user)->active('new-dashboard');
        }
        
        $endTime = microtime(true);
        $totalTime = ($endTime - $startTime) * 1000;
        $avgTime = $totalTime / $iterations;

        $this->assertLessThan(
            self::ACCEPTABLE_THRESHOLD,
            $avgTime,
            "Average feature flag check time: {$avgTime}ms"
        );
        
        echo "\n✓ Single feature flag check ({$iterations} checks): {$avgTime}ms average";
    }

    /**
     * Test: Multiple feature flag checks performance
     */
    public function test_multiple_feature_flag_checks_performance(): void
    {
        $user = User::factory()->create();
        $featureNames = ['new-dashboard', 'beta-features', 'api-v2', 'dark-mode', 'notifications'];
        $iterations = 200;

        $startTime = microtime(true);
        
        for ($i = 0; $i < $iterations; $i++) {
            foreach ($featureNames as $featureName) {
                Feature::for($user)->active($featureName);
            }
        }
        
        $endTime = microtime(true);
        $totalTime = ($endTime - $startTime) * 1000;
        $avgTime = $totalTime / ($iterations * count($featureNames));

        $this->assertLessThan(
            self::ACCEPTABLE_THRESHOLD,
            $avgTime,
            "Average multi-feature check time: {$avgTime}ms"
        );
        
        echo "\n✓ Multiple feature flag checks ({$iterations} iterations, 5 features): {$avgTime}ms average";
    }

    /**
     * Test: Feature flag value retrieval performance
     */
    public function test_feature_flag_value_performance(): void
    {
        $user = User::factory()->create();
        $iterations = 500;

        $startTime = microtime(true);
        
        for ($i = 0; $i < $iterations; $i++) {
            Feature::for($user)->value('api-version');
        }
        
        $endTime = microtime(true);
        $totalTime = ($endTime - $startTime) * 1000;
        $avgTime = $totalTime / $iterations;

        $this->assertLessThan(
            self::ACCEPTABLE_THRESHOLD,
            $avgTime,
            "Average feature flag value retrieval time: {$avgTime}ms"
        );
        
        echo "\n✓ Feature flag value retrieval ({$iterations} checks): {$avgTime}ms average";
    }

    /**
     * Test: Feature flag for multiple users performance
     */
    public function test_feature_flag_multiple_users_performance(): void
    {
        $users = User::factory()->count(100)->create();
        $featureName = 'new-dashboard';

        $startTime = microtime(true);
        
        foreach ($users as $user) {
            Feature::for($user)->active($featureName);
        }
        
        $endTime = microtime(true);
        $totalTime = ($endTime - $startTime) * 1000;
        $avgTime = $totalTime / $users->count();

        $this->assertLessThan(
            self::ACCEPTABLE_THRESHOLD,
            $avgTime,
            "Average feature flag check per user: {$avgTime}ms"
        );
        
        echo "\n✓ Feature flag for multiple users (100 users): {$avgTime}ms average";
    }
}


