# How to Run Tests - Quick Guide

This guide shows you how to manually run and verify the test suite, even if you're not familiar with PHP.

## Prerequisites

Make sure Docker containers are running:
```bash
cd apps/laravel-feature-lab
docker compose up -d
```

## Running Tests

### 1. Run All Tests

To run the entire test suite:
```bash
docker compose run --rm workspace php artisan test
```

**What this does:**
- Runs all tests in the `tests/` directory
- Shows which tests pass (✓) or fail (✗)
- Displays a summary at the end

### 2. Run Only Feature Flag Tests

To run just the feature flag tests:
```bash
docker compose run --rm workspace php artisan test --filter FeatureFlagTest
```

**Expected output:**
```
PASS  Tests\Feature\FeatureFlagTest
✓ simple boolean flags
✓ callback based flags
✓ per user flags
... (all 17 tests listed)
Tests:    17 passed (30 assertions)
```

### 3. Run a Specific Test

To run a single test method:
```bash
docker compose run --rm workspace php artisan test --filter test_simple_boolean_flags
```

### 4. Run Tests with More Details

To see more detailed output:
```bash
docker compose run --rm workspace php artisan test --filter FeatureFlagTest --verbose
```

## Understanding Test Results

### ✅ Passing Test
```
✓ simple boolean flags    0.14s
```
- The checkmark (✓) means the test passed
- The time shows how long it took to run

### ❌ Failing Test
```
✗ callback based flags    0.01s
FAILED: Expected true but got false
```
- The X (✗) means the test failed
- Error message explains what went wrong

### Summary Line
```
Tests:    17 passed (30 assertions)
Duration: 0.33s
```
- **17 passed**: All 17 tests in the suite passed
- **30 assertions**: Total number of individual checks made
- **Duration**: Total time to run all tests

## What Each Feature Flag Test Does

1. **simple boolean flags** - Tests basic on/off flags
2. **callback based flags** - Tests flags that use logic/callbacks
3. **per user flags** - Tests flags that target specific users
4. **vip access flag** - Tests VIP user targeting
5. **percentage rollout** - Tests gradual feature rollouts
6. **environment based flags** - Tests flags based on environment
7. **role based flags** - Tests flags based on user roles
8. **date based flags** - Tests time-based flags
9. **ab testing flags** - Tests A/B testing scenarios
10. **three way ab test** - Tests multi-variant A/B tests
11. **feature flags with values** - Tests flags that return values (not just true/false)
12. **theme preference flag** - Tests value-based flags
13. **complex conditional flags** - Tests flags with multiple conditions
14. **beta program flag** - Tests OR logic in flags
15. **global flags** - Tests flags without user context
16. **programmatic flag control** - Tests activating/deactivating flags in code
17. **advanced search requires verification** - Tests email verification requirement

## Troubleshooting

### Tests Fail to Run
If you get an error about containers not running:
```bash
docker compose up -d
```

### Database Connection Errors
Make sure the database container is running:
```bash
docker compose ps
```

### Clear Test Cache
If tests behave unexpectedly, clear the cache:
```bash
docker compose run --rm workspace php artisan cache:clear
docker compose run --rm workspace php artisan config:clear
```

## Quick Verification Commands

**Check if all feature flag tests pass:**
```bash
docker compose run --rm workspace php artisan test --filter FeatureFlagTest
```

**Run all tests in the project:**
```bash
docker compose run --rm workspace php artisan test
```

**See test coverage (if configured):**
```bash
docker compose run --rm workspace php artisan test --coverage
```

## What Success Looks Like

When everything is working correctly, you should see:
- All tests marked with ✓ (checkmarks)
- "PASS" at the top
- Summary showing "X passed" with no failures
- No error messages

Example of successful output:
```
PASS  Tests\Feature\FeatureFlagTest
✓ simple boolean flags
✓ callback based flags
... (all tests listed)
Tests:    17 passed (30 assertions)
Duration: 0.33s
```

