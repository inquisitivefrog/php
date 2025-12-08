<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Pennant\Feature;
use Tests\TestCase;

class FeatureFlagDemoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Feature::purge();
        DB::table('features')->truncate();
    }

    /**
     * Test: GET api/demo/dashboard
     * Demonstrates: Simple boolean feature flag check
     */
    public function test_dashboard_endpoint(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/demo/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'dashboard',
                'message',
            ]);
    }

    /**
     * Test: GET api/demo/beta-features
     * Demonstrates: Feature flag with access gating (403)
     */
    public function test_beta_features_endpoint_allowed(): void
    {
        $user = User::factory()->create(['created_at' => Carbon::parse('2024-06-01')]);
        Feature::purge();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/demo/beta-features');

        // User created after 2024-01-01 should have beta-features
        if (Feature::for($user)->active('beta-features')) {
            $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'features',
                ]);
        } else {
            $response->assertStatus(403)
                ->assertJsonStructure(['error']);
        }
    }

    public function test_beta_features_endpoint_denied(): void
    {
        $user = User::factory()->create(['created_at' => Carbon::parse('2023-01-01')]);
        Feature::purge();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/demo/beta-features');

        // User created before 2024-01-01 should not have beta-features
        if (!Feature::for($user)->active('beta-features')) {
            $response->assertStatus(403)
                ->assertJsonStructure(['error']);
        }
    }

    /**
     * Test: GET api/demo/api-info
     * Demonstrates: Feature flag returning values
     */
    public function test_api_info_endpoint(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/demo/api-info');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'api_version',
                'rate_limit',
                'rate_limit_period',
            ]);
    }

    /**
     * Test: GET api/demo/ab-test
     * Demonstrates: A/B testing with feature flags
     */
    public function test_ab_test_endpoint(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/demo/ab-test');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'variant',
                'message',
                'user_id',
            ]);
        
        $variant = $response->json('variant');
        $this->assertContains($variant, ['A', 'B']);
    }

    /**
     * Test: GET api/demo/theme
     * Demonstrates: Feature flag returning theme preference
     */
    public function test_theme_endpoint(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/demo/theme');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'theme',
                'message',
            ]);
    }

    /**
     * Test: GET api/demo/user-features
     * Demonstrates: Multiple feature flags check
     */
    public function test_user_features_endpoint(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/demo/user-features');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user_id',
                'user_email',
                'features',
                'active_feature_count',
            ]);
        
        $features = $response->json('features');
        $this->assertIsArray($features);
        $this->assertArrayHasKey('new_dashboard', $features);
    }

    /**
     * Test: GET api/demo/system-status
     * Demonstrates: Global feature flags (no user context)
     */
    public function test_system_status_endpoint(): void
    {
        $response = $this->getJson('/api/demo/system-status');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'maintenance_mode',
                'registration_enabled',
                'api_version',
                'debug_mode',
            ]);
    }

    /**
     * Test: GET api/demo/seasonal-features
     * Demonstrates: Date-based feature flags
     */
    public function test_seasonal_features_endpoint(): void
    {
        $response = $this->getJson('/api/demo/seasonal-features');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'holiday_theme',
                'summer_promotion',
                'current_month',
                'current_date',
            ]);
    }

    /**
     * Test: POST api/demo/toggle-feature/{featureName}
     * Demonstrates: Programmatically toggle feature flags
     */
    public function test_toggle_feature_endpoint(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        // Toggle a feature
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/demo/toggle-feature/new-dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'feature',
                'user_id',
                'previous_state',
                'current_state',
                'message',
            ])
            ->assertJson([
                'feature' => 'new-dashboard',
                'user_id' => $user->id,
            ]);

        // Toggle again to verify it works both ways
        $response2 = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/demo/toggle-feature/new-dashboard');

        $response2->assertStatus(200)
            ->assertJson([
                'previous_state' => !$response->json('previous_state'),
                'current_state' => !$response->json('current_state'),
            ]);
    }

    /**
     * Test: All endpoints require authentication
     */
    public function test_endpoints_require_authentication(): void
    {
        // System status and seasonal features don't require auth
        $this->getJson('/api/demo/system-status')->assertStatus(200);
        $this->getJson('/api/demo/seasonal-features')->assertStatus(200);

        // All others require authentication
        $this->getJson('/api/demo/dashboard')->assertStatus(401);
        $this->getJson('/api/demo/beta-features')->assertStatus(401);
        $this->getJson('/api/demo/api-info')->assertStatus(401);
        $this->getJson('/api/demo/ab-test')->assertStatus(401);
        $this->getJson('/api/demo/theme')->assertStatus(401);
        $this->getJson('/api/demo/user-features')->assertStatus(401);
        $this->postJson('/api/demo/toggle-feature/test')->assertStatus(401);
    }
}

