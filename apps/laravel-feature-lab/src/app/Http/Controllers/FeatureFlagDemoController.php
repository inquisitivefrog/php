<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Pennant\Feature;

/**
 * Demo controller showing various ways to use Pennant feature flags
 */
class FeatureFlagDemoController extends Controller
{
    /**
     * Example 1: Simple boolean check
     * 
     * Demonstrates checking a simple feature flag
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();

        if (Feature::active('new-dashboard', $user)) {
            return response()->json([
                'dashboard' => 'new',
                'message' => 'You are seeing the new dashboard',
            ]);
        }

        return response()->json([
            'dashboard' => 'legacy',
            'message' => 'You are seeing the legacy dashboard',
        ]);
    }

    /**
     * Example 2: Feature flag with early return (403)
     * 
     * Demonstrates gating access to a feature
     */
    public function betaFeatures(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!Feature::active('beta-features', $user)) {
            return response()->json([
                'error' => 'Beta features are not available for your account',
            ], 403);
        }

        return response()->json([
            'message' => 'Welcome to beta features!',
            'features' => ['feature-1', 'feature-2', 'feature-3'],
        ]);
    }

    /**
     * Example 3: Feature flag returning a value (not just boolean)
     * 
     * Demonstrates getting a value from a feature flag
     */
    public function apiInfo(Request $request): JsonResponse
    {
        $user = $request->user();

        $rateLimit = Feature::value('api-rate-limit', $user);
        $apiVersion = Feature::value('api-version');

        return response()->json([
            'api_version' => $apiVersion,
            'rate_limit' => $rateLimit,
            'rate_limit_period' => 'per hour',
        ]);
    }

    /**
     * Example 4: A/B testing
     * 
     * Demonstrates using feature flags for A/B testing
     */
    public function abTest(Request $request): JsonResponse
    {
        $user = $request->user();

        $variant = Feature::active('ab-test-variant-a', $user) ? 'A' : 'B';

        return response()->json([
            'variant' => $variant,
            'message' => "You are in variant {$variant}",
            'user_id' => $user->id,
        ]);
    }

    /**
     * Example 5: Theme preference
     * 
     * Demonstrates feature flag returning different values
     */
    public function theme(Request $request): JsonResponse
    {
        $user = $request->user();

        $theme = Feature::value('theme-preference', $user);

        return response()->json([
            'theme' => $theme,
            'message' => "Your theme preference: {$theme}",
        ]);
    }

    /**
     * Example 6: Conditional feature based on user properties
     * 
     * Demonstrates checking multiple feature flags
     */
    public function userFeatures(Request $request): JsonResponse
    {
        $user = $request->user();

        $features = [
            'new_dashboard' => Feature::active('new-dashboard', $user),
            'beta_features' => Feature::active('beta-features', $user),
            'early_access' => Feature::active('early-access', $user),
            'vip_access' => Feature::active('vip-access', $user),
            'advanced_search' => Feature::active('advanced-search', $user),
            'new_ui' => Feature::active('new-ui', $user),
            'admin_panel' => Feature::active('admin-panel', $user),
        ];

        return response()->json([
            'user_id' => $user->id,
            'user_email' => $user->email,
            'features' => $features,
            'active_feature_count' => count(array_filter($features)),
        ]);
    }

    /**
     * Example 7: Global feature flag (no user context)
     * 
     * Demonstrates checking global feature flags
     */
    public function systemStatus(): JsonResponse
    {
        return response()->json([
            'maintenance_mode' => Feature::active('maintenance-mode'),
            'registration_enabled' => Feature::active('enable-registration'),
            'api_version' => Feature::value('api-version'),
            'debug_mode' => Feature::active('debug-mode'),
        ]);
    }

    /**
     * Example 8: Date-based feature flag
     * 
     * Demonstrates time-based feature flags
     */
    public function seasonalFeatures(): JsonResponse
    {
        return response()->json([
            'holiday_theme' => Feature::active('holiday-theme'),
            'summer_promotion' => Feature::active('summer-promotion'),
            'current_month' => now()->month,
            'current_date' => now()->toDateString(),
        ]);
    }

    /**
     * Example 9: Programmatically activate/deactivate features
     * 
     * Demonstrates managing feature flags programmatically
     * Note: In production, use Artisan commands or admin panel
     */
    public function toggleFeature(Request $request, string $featureName): JsonResponse
    {
        $user = $request->user();

        // Check if feature is currently active
        $wasActive = Feature::active($featureName, $user);

        // Toggle the feature
        if ($wasActive) {
            Feature::deactivate($featureName, $user);
            $message = "Feature '{$featureName}' deactivated for user {$user->id}";
        } else {
            Feature::activate($featureName, $user);
            $message = "Feature '{$featureName}' activated for user {$user->id}";
        }

        return response()->json([
            'feature' => $featureName,
            'user_id' => $user->id,
            'previous_state' => $wasActive,
            'current_state' => !$wasActive,
            'message' => $message,
        ]);
    }
}
