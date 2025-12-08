# Pennant Feature Flags - Complete Guide

This document demonstrates all available feature flag patterns implemented in the Laravel Feature Lab project.

## Overview

Pennant provides a powerful feature flag system for Laravel applications. Feature flags allow you to:
- Gradually roll out new features
- A/B test different versions
- Enable features for specific users or groups
- Control feature availability based on conditions
- Manage feature lifecycle without code deployments

## Feature Flag Patterns

### 1. Simple Boolean Flags

The simplest form - always enabled or disabled.

```php
Feature::define('new-dashboard', true);
Feature::define('legacy-api', false);
```

**Usage:**
```php
if (Feature::active('new-dashboard')) {
    // Show new dashboard
}
```

### 2. Callback-Based Flags with Conditions

Enable features based on application logic.

```php
Feature::define('beta-features', function (User $user) {
    return $user->created_at->isAfter('2024-01-01');
});
```

**Usage:**
```php
if (Feature::active('beta-features', $user)) {
    // Show beta features
}
```

### 3. Per-User Flags (Explicit Targeting)

Enable for specific users by email, ID, or other criteria.

```php
Feature::define('early-access', function (User $user) {
    $earlyAccessUsers = ['admin@example.com', 'beta@example.com'];
    return in_array($user->email, $earlyAccessUsers);
});
```

**Usage:**
```php
if (Feature::active('early-access', $user)) {
    // Grant early access
}
```

### 4. Percentage-Based Rollouts

Gradually roll out to a percentage of users.

```php
Feature::define('new-ui', function (User $user) {
    // Roll out to 25% of users
    return (crc32($user->id) % 100) < 25;
});
```

**Usage:**
```php
if (Feature::active('new-ui', $user)) {
    // Show new UI
}
```

### 5. Environment-Based Flags

Enable only in specific environments.

```php
Feature::define('debug-mode', function () {
    return app()->environment(['local', 'staging']);
});
```

**Usage:**
```php
if (Feature::active('debug-mode')) {
    // Enable debug features
}
```

### 6. Role-Based Flags

Enable based on user roles.

```php
Feature::define('admin-panel', function (User $user) {
    return str_ends_with($user->email, '@admin.example.com');
});
```

**Usage:**
```php
if (Feature::active('admin-panel', $user)) {
    // Show admin panel
}
```

### 7. Date-Based Flags

Enable/disable based on dates.

```php
Feature::define('holiday-theme', function () {
    return now()->month === 12;
});
```

**Usage:**
```php
if (Feature::active('holiday-theme')) {
    // Apply holiday theme
}
```

### 8. A/B Testing Flags

Split users into groups for A/B testing.

```php
Feature::define('ab-test-variant-a', function (User $user) {
    return (crc32($user->id) % 2) === 0;
});
```

**Usage:**
```php
if (Feature::active('ab-test-variant-a', $user)) {
    // Show variant A
} else {
    // Show variant B
}
```

### 9. Subscription-Based Flags

Enable premium features based on subscription (for Cashier integration).

```php
Feature::define('premium-analytics', function (User $user) {
    return $user->subscribed('default');
});
```

**Usage:**
```php
if (Feature::active('premium-analytics', $user)) {
    // Show premium analytics
}
```

### 10. Complex Conditional Flags

Combine multiple conditions.

```php
Feature::define('advanced-features', function (User $user) {
    return $user->email_verified_at !== null
        && $user->created_at->isAfter('2024-01-01')
        && app()->environment('production');
});
```

### 11. Feature Flags with Values

Return different values, not just boolean.

```php
Feature::define('api-rate-limit', function (User $user) {
    if (str_contains($user->email, 'premium')) {
        return 1000;
    }
    return 100;
});
```

**Usage:**
```php
$rateLimit = Feature::value('api-rate-limit', $user);
// Returns: 1000 or 100
```

### 12. Global Flags (No User Context)

Flags that don't depend on user.

```php
Feature::define('maintenance-mode', function () {
    return config('app.maintenance', false);
});
```

**Usage:**
```php
if (Feature::active('maintenance-mode')) {
    // Show maintenance page
}
```

## Checking Feature Flags

### In Controllers

```php
use Laravel\Pennant\Feature;

public function index(User $user)
{
    if (Feature::active('new-dashboard', $user)) {
        return view('dashboard.new');
    }
    
    return view('dashboard.old');
}
```

### In Blade Templates

```blade
@feature('new-dashboard', $user)
    <div>New Dashboard Content</div>
@endfeature
```

### In Middleware

```php
use Laravel\Pennant\Feature;

public function handle($request, Closure $next)
{
    if (!Feature::active('beta-features', $request->user())) {
        abort(403, 'Beta features not available');
    }
    
    return $next($request);
}
```

### In API Routes

```php
Route::get('/api/analytics', function (Request $request) {
    $user = $request->user();
    
    if (!Feature::active('premium-analytics', $user)) {
        return response()->json(['error' => 'Premium feature'], 403);
    }
    
    return response()->json(['data' => 'analytics data']);
})->middleware('auth:sanctum');
```

## Managing Feature Flags

### Via Artisan Commands

```bash
# List all feature flags
php artisan pennant:feature

# Set a feature flag value
php artisan pennant:feature new-dashboard --activate

# Deactivate a feature flag
php artisan pennant:feature new-dashboard --deactivate

# Set for specific user
php artisan pennant:feature early-access --activate --user=1

# Set percentage rollout
php artisan pennant:feature new-ui --activate --percentage=25

# Clear feature cache
php artisan pennant:clear

# Purge all feature flags
php artisan pennant:purge
```

### Programmatically

```php
use Laravel\Pennant\Feature;

// Activate for all users
Feature::activate('new-dashboard');

// Activate for specific user
Feature::activate('early-access', $user);

// Deactivate
Feature::deactivate('new-dashboard');

// Activate for percentage
Feature::activate('new-ui', percentage: 25);
```

## Testing Feature Flags

### In Tests

```php
use Laravel\Pennant\Feature;
use Tests\TestCase;

class FeatureTest extends TestCase
{
    public function test_user_can_access_beta_features()
    {
        $user = User::factory()->create();
        
        // Activate feature for user
        Feature::activate('beta-features', $user);
        
        $this->assertTrue(Feature::active('beta-features', $user));
    }
    
    public function test_user_cannot_access_beta_features()
    {
        $user = User::factory()->create();
        
        // Feature is inactive by default
        $this->assertFalse(Feature::active('beta-features', $user));
    }
}
```

## Available Feature Flags in This Project

All feature flags are defined in `app/Providers/FeatureServiceProvider.php`:

1. **new-dashboard** - Simple boolean flag
2. **legacy-api** - Simple boolean flag (disabled)
3. **beta-features** - Date-based user targeting
4. **advanced-search** - Email verification check
5. **early-access** - Email-based user targeting
6. **vip-access** - ID-based user targeting
7. **new-ui** - 25% percentage rollout
8. **experimental-feature** - 10% percentage rollout
9. **debug-mode** - Environment-based (local/staging)
10. **production-only** - Environment-based (production)
11. **admin-panel** - Role-based (email check)
12. **moderator-tools** - Role-based (email check)
13. **holiday-theme** - Date-based (December)
14. **summer-promotion** - Date-based (June-August)
15. **launch-date** - Date-based (after 2025-01-15)
16. **ab-test-variant-a** - A/B testing (50% split)
17. **ab-test-variant-b** - A/B testing (50% split)
18. **ab-test-three-way** - A/B testing (3-way split)
19. **premium-analytics** - Subscription-based (disabled until Cashier)
20. **team-collaboration** - Subscription-based (disabled until Cashier)
21. **unlimited-tasks** - Subscription-based (disabled until Cashier)
22. **advanced-features** - Complex conditional
23. **beta-program** - Complex conditional (OR logic)
24. **api-rate-limit** - Value-based (returns number)
25. **theme-preference** - Value-based (returns string)
26. **maintenance-mode** - Global flag (config-based)
27. **enable-registration** - Global flag (config-based)
28. **api-version** - Global flag (returns version string)

## Best Practices

1. **Use descriptive names**: `new-dashboard` is better than `nd`
2. **Document your flags**: Add comments explaining why flags exist
3. **Clean up old flags**: Remove flags that are no longer needed
4. **Test flag logic**: Write tests for complex flag conditions
5. **Monitor flag usage**: Track which flags are active in production
6. **Use percentage rollouts**: Gradually roll out to avoid issues
7. **Combine flags carefully**: Complex conditions can be hard to debug

## Resources

- [Laravel Pennant Documentation](https://laravel.com/docs/pennant)
- [Feature Flag Best Practices](https://martinfowler.com/articles/feature-toggles.html)








