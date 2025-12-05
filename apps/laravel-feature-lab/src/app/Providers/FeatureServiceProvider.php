<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\ServiceProvider;
use Laravel\Pennant\Feature;

class FeatureServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * This method demonstrates all available Pennant feature flag patterns:
     * 1. Simple boolean flags
     * 2. Callback-based flags with conditions
     * 3. Per-user flags
     * 4. Percentage-based rollouts
     * 5. Environment-based flags
     * 6. Role-based flags
     * 7. Date-based flags
     * 8. A/B testing flags
     * 9. Subscription-based flags
     */
    public function boot(): void
    {
        // ============================================================
        // 1. SIMPLE BOOLEAN FLAGS
        // ============================================================
        // Always enabled or disabled
        Feature::define('new-dashboard', true);
        Feature::define('legacy-api', false);

        // ============================================================
        // 2. CALLBACK-BASED FLAGS WITH CONDITIONS
        // ============================================================
        // Enable based on application logic
        Feature::define('beta-features', function (User $user) {
            // Enable for users created after a certain date
            if (!$user->created_at) {
                return false;
            }
            return $user->created_at->isAfter(\Carbon\Carbon::parse('2024-01-01'));
        });

        Feature::define('advanced-search', function (User $user) {
            // Enable based on user attribute
            return $user->email_verified_at !== null;
        });

        // ============================================================
        // 3. PER-USER FLAGS (Explicit User Targeting)
        // ============================================================
        // Enable for specific users by ID or email
        Feature::define('early-access', function (User $user) {
            $earlyAccessUsers = [
                'admin@example.com',
                'beta@example.com',
            ];

            return in_array($user->email, $earlyAccessUsers);
        });

        Feature::define('vip-access', function (User $user) {
            // Enable for users with specific IDs
            $vipUserIds = [1, 2, 3];
            return in_array($user->id, $vipUserIds);
        });

        // ============================================================
        // 4. PERCENTAGE-BASED ROLLOUTS
        // ============================================================
        // Gradually roll out to a percentage of users
        Feature::define('new-ui', function (User $user) {
            // Roll out to 25% of users based on user ID hash
            return (crc32($user->id) % 100) < 25;
        });

        Feature::define('experimental-feature', function (User $user) {
            // Roll out to 10% of users
            return (crc32($user->email) % 100) < 10;
        });

        // ============================================================
        // 5. ENVIRONMENT-BASED FLAGS
        // ============================================================
        // Enable only in specific environments
        Feature::define('debug-mode', function () {
            return app()->environment(['local', 'staging']);
        });

        Feature::define('production-only', function () {
            return app()->environment('production');
        });

        // ============================================================
        // 6. ROLE-BASED FLAGS
        // ============================================================
        // Enable based on user roles (when roles are implemented)
        Feature::define('admin-panel', function (User $user) {
            // Example: Check if user has admin role
            // This assumes you'll add a role system later
            // For now, check by email or add a role column
            return str_ends_with($user->email, '@admin.example.com');
        });

        Feature::define('moderator-tools', function (User $user) {
            // Enable for moderators
            return str_contains($user->email, 'moderator');
        });

        // ============================================================
        // 7. DATE-BASED FLAGS
        // ============================================================
        // Enable/disable based on dates
        Feature::define('holiday-theme', function () {
            $now = now();
            // Enable during December
            return $now->month === 12;
        });

        Feature::define('summer-promotion', function () {
            $now = now();
            // Enable from June 1 to August 31
            return $now->month >= 6 && $now->month <= 8;
        });

        Feature::define('launch-date', function () {
            // Enable after a specific launch date
            return now()->isAfter('2025-01-15');
        });

        // ============================================================
        // 8. A/B TESTING FLAGS
        // ============================================================
        // Split users into groups for A/B testing
        Feature::define('ab-test-variant-a', function (User $user) {
            // 50% of users get variant A
            return (crc32($user->id) % 2) === 0;
        });

        Feature::define('ab-test-variant-b', function (User $user) {
            // 50% of users get variant B (opposite of A)
            return (crc32($user->id) % 2) === 1;
        });

        Feature::define('ab-test-three-way', function (User $user) {
            // Split into 3 groups: 0-33, 34-66, 67-99
            $hash = crc32($user->id) % 100;
            if ($hash < 33) {
                return 'variant-a';
            } elseif ($hash < 66) {
                return 'variant-b';
            } else {
                return 'variant-c';
            }
        });

        // ============================================================
        // 9. SUBSCRIPTION-BASED FLAGS (Cashier Integration)
        // ============================================================
        // Enable premium features based on subscription
        Feature::define('premium-analytics', function (User $user) {
            // Enable for users with any active subscription
            return $user->subscribed();
        });

        Feature::define('team-collaboration', function (User $user) {
            // Enable for Pro tier subscribers
            // Replace 'price_pro_tier' with your actual Stripe price ID
            // For demo purposes, check if user has any subscription
            return $user->subscribed();
        });

        Feature::define('unlimited-tasks', function (User $user) {
            // Enable for paid subscribers (any active subscription)
            return $user->subscribed();
        });

        // ============================================================
        // 10. COMPLEX CONDITIONAL FLAGS
        // ============================================================
        // Combine multiple conditions
        Feature::define('advanced-features', function (User $user) {
            // Enable if user is verified AND created after a date AND in production
            return $user->email_verified_at !== null
                && $user->created_at && $user->created_at->isAfter(\Carbon\Carbon::parse('2024-01-01'))
                && app()->environment('production');
        });

        Feature::define('beta-program', function (User $user) {
            // Enable for early users OR VIP users OR in staging
            $isEarlyUser = $user->created_at && $user->created_at->isBefore(\Carbon\Carbon::parse('2024-06-01'));
            $isVip = in_array($user->email, ['admin@example.com', 'beta@example.com']);
            $isStaging = app()->environment('staging');

            return $isEarlyUser || $isVip || $isStaging;
        });

        // ============================================================
        // 11. FEATURE FLAGS WITH VALUES (Not Just Boolean)
        // ============================================================
        // Return different values, not just true/false
        Feature::define('api-rate-limit', function (User $user) {
            // Return different rate limits based on user type
            if (str_contains($user->email, 'premium')) {
                return 1000; // Premium users get 1000 requests/hour
            } elseif ($user->email_verified_at) {
                return 500; // Verified users get 500 requests/hour
            } else {
                return 100; // Default users get 100 requests/hour
            }
        });

        Feature::define('theme-preference', function (User $user) {
            // Return theme name based on user
            $hash = crc32($user->id) % 3;
            return match ($hash) {
                0 => 'light',
                1 => 'dark',
                2 => 'auto',
            };
        });

        // ============================================================
        // 12. FLAGS WITHOUT USER CONTEXT (Global Flags)
        // ============================================================
        // Flags that don't depend on user
        Feature::define('maintenance-mode', function () {
            return config('app.maintenance', false);
        });

        Feature::define('enable-registration', function () {
            return config('app.registration_enabled', true);
        });

        Feature::define('api-version', function () {
            // Return API version string
            return 'v2';
        });
    }
}
