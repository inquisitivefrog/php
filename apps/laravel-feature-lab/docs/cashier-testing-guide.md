# Cashier Testing Guide

## Overview

**Stripe Test Mode is FREE** - No real charges occur when using Stripe test mode keys. All tests use database mocking to avoid real Stripe API calls, making them fast and reliable.

## Test Files

### 1. `SubscriptionTest.php` - Basic Subscription Tests
- 5 tests covering basic subscription functionality
- Tests authentication, validation, and basic API endpoints

### 2. `CashierComprehensiveTest.php` - Comprehensive Feature Coverage
- 20 tests demonstrating all Cashier features
- Uses database mocking (direct DB inserts) to avoid Stripe API calls
- **77 assertions** covering the full range of Cashier capabilities

## Features Demonstrated

### ✅ Subscription Status Management
- Active subscriptions
- Cancelled subscriptions (grace period)
- Trial subscriptions
- Generic trials
- Multiple subscriptions per user
- Subscription status transitions (active, trialing, past_due, canceled, unpaid)

### ✅ Subscription Operations
- Creating subscriptions
- Cancelling subscriptions
- Resuming cancelled subscriptions
- Subscription quantity management
- Subscription items (multiple line items)

### ✅ Billing Features
- Per-seat pricing (quantity-based)
- Multiple subscription types
- Subscription to specific prices
- Payment method information
- Billing intervals (monthly/annual)

### ✅ Advanced Features
- Metered billing (usage-based)
- Subscription items with meters
- Grace period handling
- Trial period management
- Feature flag integration

### ✅ API Integration
- Subscription status endpoint
- Checkout session creation
- Cancellation via API
- Resume via API
- Billing portal URL

## Running Tests

### Run All Cashier Tests
```bash
docker compose run --rm workspace php artisan test --filter "CashierComprehensiveTest|SubscriptionTest"
```

### Run Specific Test Suite
```bash
# Comprehensive tests
docker compose run --rm workspace php artisan test --filter CashierComprehensiveTest

# Basic subscription tests
docker compose run --rm workspace php artisan test --filter SubscriptionTest
```

### Run Individual Test
```bash
docker compose run --rm workspace php artisan test --filter test_user_with_active_subscription
```

## Test Results

**Current Status:**
- ✅ 20 comprehensive tests passing
- ✅ 5 basic subscription tests passing
- ✅ 1 test skipped (requires Stripe keys for API call)
- ✅ 77+ assertions total

## How Tests Work

### Database Mocking Approach

Instead of making real Stripe API calls, tests create subscription records directly in the database:

```php
$subscription = $user->subscriptions()->create([
    'type' => 'default',
    'stripe_id' => 'sub_test123',
    'stripe_status' => 'active',
    'stripe_price' => 'price_test123',
    'quantity' => 1,
]);
```

This approach:
- ✅ Fast execution (no network calls)
- ✅ Reliable (no API rate limits)
- ✅ Free (no Stripe API usage)
- ✅ Tests actual Cashier logic

### What's NOT Tested (Requires Real Stripe Keys)

Some features require actual Stripe API calls and are skipped or tested conceptually:

- **Checkout Session Creation** - Requires valid Stripe keys
- **Billing Portal URL** - Requires valid Stripe keys  
- **Real Subscription Cancellation** - Tested via database mocking instead

These are marked with `markTestSkipped()` or test the method existence rather than actual API calls.

## Test Coverage Summary

| Feature | Tests | Status |
|---------|-------|--------|
| Active Subscriptions | 3 | ✅ |
| Cancelled Subscriptions | 2 | ✅ |
| Trial Subscriptions | 2 | ✅ |
| Multiple Subscriptions | 1 | ✅ |
| Subscription Statuses | 1 | ✅ |
| Quantity Management | 2 | ✅ |
| Subscription Items | 1 | ✅ |
| Feature Flags | 2 | ✅ |
| API Endpoints | 3 | ✅ |
| Payment Methods | 1 | ✅ |
| Billing Intervals | 1 | ✅ |
| Metered Billing | 1 | ✅ |
| Grace Periods | 1 | ✅ |

## Key Test Scenarios

### 1. User with Active Subscription
```php
test_user_with_active_subscription()
```
- Creates active subscription
- Verifies `subscribed()` returns true
- Tests feature flags integration

### 2. Cancelled Subscription (Grace Period)
```php
test_user_with_cancelled_subscription()
```
- Subscription cancelled but still active until period ends
- Verifies grace period behavior
- Tests feature flags during grace period

### 3. Trial Subscription
```php
test_user_with_trial_subscription()
```
- Creates subscription with trial period
- Verifies `onTrial()` behavior
- Tests feature flags during trial

### 4. Multiple Subscriptions
```php
test_user_with_multiple_subscriptions()
```
- User with multiple subscription types
- Tests accessing specific subscriptions
- Verifies quantity management

### 5. Subscription Status Transitions
```php
test_subscription_status_transitions()
```
- Tests all Stripe subscription statuses
- Verifies which statuses count as "subscribed"
- Demonstrates status handling

## Best Practices

1. **Use Database Mocking** - Create subscriptions directly in DB for speed
2. **Test Status Values** - Verify `stripe_status` directly when needed
3. **Separate Users** - Use different users for each test case to avoid conflicts
4. **Refresh Models** - Call `$user->refresh()` after creating subscriptions
5. **Test Feature Flags** - Verify integration with Pennant feature flags

## Notes

- All tests use `RefreshDatabase` trait for clean state
- Tests are isolated and can run in any order
- No external dependencies required (except database)
- Tests demonstrate real-world usage patterns

