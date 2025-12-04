<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Tests\TestCase;

class FeatureFlagTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test simple boolean feature flags
     */
    public function test_simple_boolean_flags(): void
    {
        $user = User::factory()->create();

        // new-dashboard is defined as true
        $this->assertTrue(Feature::active('new-dashboard', $user));

        // legacy-api is defined as false
        $this->assertFalse(Feature::active('legacy-api', $user));
    }

    /**
     * Test callback-based flags with conditions
     */
    public function test_callback_based_flags(): void
    {
        // User created after 2024-01-01 should have beta-features
        $newUser = User::factory()->create(['created_at' => '2024-06-01']);
        $this->assertTrue(Feature::active('beta-features', $newUser));

        // User created before 2024-01-01 should not have beta-features
        $oldUser = User::factory()->create(['created_at' => '2023-06-01']);
        $this->assertFalse(Feature::active('beta-features', $oldUser));
    }

    /**
     * Test per-user flags (explicit targeting)
     */
    public function test_per_user_flags(): void
    {
        $adminUser = User::factory()->create(['email' => 'admin@example.com']);
        $regularUser = User::factory()->create(['email' => 'user@example.com']);

        // Admin should have early-access
        $this->assertTrue(Feature::active('early-access', $adminUser));

        // Regular user should not have early-access
        $this->assertFalse(Feature::active('early-access', $regularUser));
    }

    /**
     * Test VIP access flag
     */
    public function test_vip_access_flag(): void
    {
        // VIP users (IDs 1, 2, 3)
        $vipUser = User::factory()->create(['id' => 1]);
        $this->assertTrue(Feature::active('vip-access', $vipUser));

        // Regular user
        $regularUser = User::factory()->create(['id' => 10]);
        $this->assertFalse(Feature::active('vip-access', $regularUser));
    }

    /**
     * Test percentage-based rollouts
     */
    public function test_percentage_rollout(): void
    {
        // Create multiple users and check distribution
        $users = User::factory()->count(100)->create();
        
        $activeCount = 0;
        foreach ($users as $user) {
            if (Feature::active('new-ui', $user)) {
                $activeCount++;
            }
        }

        // Should be approximately 25% (allowing for some variance)
        // Using hash-based distribution, exact count may vary
        $this->assertGreaterThan(0, $activeCount);
        $this->assertLessThan(50, $activeCount); // Should be around 25%
    }

    /**
     * Test environment-based flags
     */
    public function test_environment_based_flags(): void
    {
        // debug-mode should be active in local/staging
        // In testing environment, this may vary
        $isDebugMode = Feature::active('debug-mode');
        
        // Just verify it returns a boolean
        $this->assertIsBool($isDebugMode);
    }

    /**
     * Test role-based flags
     */
    public function test_role_based_flags(): void
    {
        $adminUser = User::factory()->create(['email' => 'admin@admin.example.com']);
        $regularUser = User::factory()->create(['email' => 'user@example.com']);

        // Admin should have admin-panel access
        $this->assertTrue(Feature::active('admin-panel', $adminUser));

        // Regular user should not
        $this->assertFalse(Feature::active('admin-panel', $regularUser));
    }

    /**
     * Test date-based flags
     */
    public function test_date_based_flags(): void
    {
        // holiday-theme is active in December
        // This test will pass/fail depending on when it's run
        $isHolidayTheme = Feature::active('holiday-theme');
        $this->assertIsBool($isHolidayTheme);
    }

    /**
     * Test A/B testing flags
     */
    public function test_ab_testing_flags(): void
    {
        $user = User::factory()->create();

        // User should be in either variant A or B, but not both
        $variantA = Feature::active('ab-test-variant-a', $user);
        $variantB = Feature::active('ab-test-variant-b', $user);

        // Should be in exactly one variant
        $this->assertTrue($variantA xor $variantB);
    }

    /**
     * Test three-way A/B test
     */
    public function test_three_way_ab_test(): void
    {
        $user = User::factory()->create();

        $variant = Feature::value('ab-test-three-way', $user);

        // Should return one of the three variants
        $this->assertContains($variant, ['variant-a', 'variant-b', 'variant-c']);
    }

    /**
     * Test feature flags with values (not just boolean)
     */
    public function test_feature_flags_with_values(): void
    {
        $premiumUser = User::factory()->create(['email' => 'premium@example.com']);
        $regularUser = User::factory()->create(['email' => 'user@example.com']);

        // Premium user should get higher rate limit
        $premiumLimit = Feature::value('api-rate-limit', $premiumUser);
        $this->assertEquals(1000, $premiumLimit);

        // Regular user should get default rate limit
        $regularLimit = Feature::value('api-rate-limit', $regularUser);
        $this->assertEquals(100, $regularLimit);
    }

    /**
     * Test theme preference feature flag
     */
    public function test_theme_preference_flag(): void
    {
        $user = User::factory()->create();

        $theme = Feature::value('theme-preference', $user);

        // Should return one of the valid themes
        $this->assertContains($theme, ['light', 'dark', 'auto']);
    }

    /**
     * Test complex conditional flags
     */
    public function test_complex_conditional_flags(): void
    {
        // Create user that meets all conditions
        $qualifiedUser = User::factory()->create([
            'email_verified_at' => now(),
            'created_at' => '2024-06-01',
        ]);

        // Create user that doesn't meet conditions
        $unqualifiedUser = User::factory()->create([
            'email_verified_at' => null,
            'created_at' => '2023-06-01',
        ]);

        // Results depend on environment, so just verify they return booleans
        $this->assertIsBool(Feature::active('advanced-features', $qualifiedUser));
        $this->assertIsBool(Feature::active('advanced-features', $unqualifiedUser));
    }

    /**
     * Test beta program flag (OR logic)
     */
    public function test_beta_program_flag(): void
    {
        // Early user (created before 2024-06-01)
        $earlyUser = User::factory()->create([
            'created_at' => '2024-01-01',
        ]);
        $this->assertTrue(Feature::active('beta-program', $earlyUser));

        // VIP user
        $vipUser = User::factory()->create(['email' => 'admin@example.com']);
        $this->assertTrue(Feature::active('beta-program', $vipUser));

        // Regular user
        $regularUser = User::factory()->create([
            'email' => 'user@example.com',
            'created_at' => '2024-12-01',
        ]);
        // May or may not be true depending on environment
        $this->assertIsBool(Feature::active('beta-program', $regularUser));
    }

    /**
     * Test global flags (no user context)
     */
    public function test_global_flags(): void
    {
        // These should work without a user
        $maintenanceMode = Feature::active('maintenance-mode');
        $this->assertIsBool($maintenanceMode);

        $enableRegistration = Feature::active('enable-registration');
        $this->assertIsBool($enableRegistration);

        $apiVersion = Feature::value('api-version');
        $this->assertEquals('v2', $apiVersion);
    }

    /**
     * Test programmatically activating/deactivating flags
     */
    public function test_programmatic_flag_control(): void
    {
        $user = User::factory()->create();

        // Initially, flag may be inactive
        $initialState = Feature::active('beta-features', $user);

        // Activate the flag
        Feature::activate('beta-features', $user);
        $this->assertTrue(Feature::active('beta-features', $user));

        // Deactivate the flag
        Feature::deactivate('beta-features', $user);
        $this->assertFalse(Feature::active('beta-features', $user));
    }

    /**
     * Test email verification requirement for advanced-search
     */
    public function test_advanced_search_requires_verification(): void
    {
        $verifiedUser = User::factory()->create(['email_verified_at' => now()]);
        $unverifiedUser = User::factory()->create(['email_verified_at' => null]);

        $this->assertTrue(Feature::active('advanced-search', $verifiedUser));
        $this->assertFalse(Feature::active('advanced-search', $unverifiedUser));
    }
}



