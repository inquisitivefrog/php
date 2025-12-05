# Laravel Cashier (Stripe) Setup Guide

This guide explains how to set up and use Laravel Cashier for Stripe subscriptions in this project.

## Installation Status

✅ **Cashier is installed and configured**

- Package: `laravel/cashier` v16.1
- Migrations: Published and run
- User Model: `Billable` trait added
- Routes: Subscription endpoints created

## Configuration

### 1. Stripe API Keys

Add your Stripe API keys to your `.env` file:

```env
STRIPE_KEY=pk_test_your_publishable_key_here
STRIPE_SECRET=sk_test_your_secret_key_here
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret_here
```

**For Testing:**
- Use Stripe test mode keys (they start with `pk_test_` and `sk_test_`)
- Get test keys from: https://dashboard.stripe.com/test/apikeys

**For Production:**
- Use live keys (they start with `pk_live_` and `sk_live_`)
- Get live keys from: https://dashboard.stripe.com/apikeys

### 2. Stripe Products and Prices

1. Go to Stripe Dashboard: https://dashboard.stripe.com/test/products
2. Create products (e.g., "Pro Plan", "Team Plan")
3. Create prices for each product
4. Copy the Price IDs (they start with `price_`)

Example Price IDs:
- `price_1234567890abcdef` - Pro Plan ($10/month)
- `price_0987654321fedcba` - Team Plan ($25/month)

## Database Tables

Cashier creates the following tables:

- `users` - Adds Stripe customer columns (`stripe_id`, `pm_type`, `pm_last_four`, `trial_ends_at`)
- `subscriptions` - Stores subscription records
- `subscription_items` - Stores subscription line items

## API Endpoints

All subscription endpoints require authentication via Sanctum.

### Get Subscription Status
```http
GET /api/subscription
Authorization: Bearer {token}
```

Response:
```json
{
  "has_subscription": false,
  "subscriptions": [],
  "on_trial": false,
  "on_generic_trial": false
}
```

### Create Checkout Session
```http
POST /api/subscription/checkout
Authorization: Bearer {token}
Content-Type: application/json

{
  "price_id": "price_1234567890abcdef"
}
```

Response:
```json
{
  "checkout_url": "https://checkout.stripe.com/...",
  "session_id": "cs_..."
}
```

### Cancel Subscription
```http
POST /api/subscription/cancel
Authorization: Bearer {token}
Content-Type: application/json

{
  "subscription": "default"
}
```

### Resume Subscription
```http
POST /api/subscription/resume
Authorization: Bearer {token}
Content-Type: application/json

{
  "subscription": "default"
}
```

### Get Billing Portal URL
```http
GET /api/subscription/portal
Authorization: Bearer {token}
```

Response:
```json
{
  "portal_url": "https://billing.stripe.com/..."
}
```

## Feature Flags Integration

Subscription status is integrated with Pennant feature flags:

- `premium-analytics` - Enabled for users with any active subscription
- `team-collaboration` - Enabled for users with any active subscription
- `unlimited-tasks` - Enabled for users with any active subscription

Check feature flags:
```php
use Laravel\Pennant\Feature;

if (Feature::for($user)->active('premium-analytics')) {
    // Show premium analytics
}
```

## Usage Examples

### Check if User is Subscribed
```php
$user = auth()->user();

if ($user->subscribed()) {
    // User has an active subscription
}

if ($user->subscribed('default')) {
    // User has subscription named 'default'
}

if ($user->subscribedToPrice('price_1234567890abcdef')) {
    // User is subscribed to specific price
}
```

### Create a Subscription
```php
$user = auth()->user();

// Create checkout session
$checkout = $user->checkout(['price_1234567890abcdef'], [
    'success_url' => route('subscription.success'),
    'cancel_url' => route('subscription.cancel'),
]);

// Redirect to checkout
return redirect($checkout->url);
```

### Cancel a Subscription
```php
$user = auth()->user();
$user->subscription('default')->cancel();
```

### Resume a Subscription
```php
$user = auth()->user();
$user->subscription('default')->resume();
```

## Webhooks

Stripe sends webhooks to notify your application of subscription events.

### Setup Webhook Endpoint

1. Go to Stripe Dashboard: https://dashboard.stripe.com/test/webhooks
2. Click "Add endpoint"
3. Set URL: `https://your-domain.com/stripe/webhook`
4. Select events to listen for:
   - `customer.subscription.created`
   - `customer.subscription.updated`
   - `customer.subscription.deleted`
   - `invoice.payment_succeeded`
   - `invoice.payment_failed`
5. Copy the webhook signing secret

### Configure Webhook Secret

Add to `.env`:
```env
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret_here
```

### Webhook Route

Cashier automatically handles webhooks at `/stripe/webhook`. Make sure this route is accessible and not protected by CSRF.

## Testing

### Run Subscription Tests
```bash
docker compose run --rm workspace php artisan test --filter SubscriptionTest
```

### Test Mode

When using Stripe test mode:
- Use test API keys
- Use test card numbers: https://stripe.com/docs/testing
- Example test card: `4242 4242 4242 4242` (any future expiry, any CVC)

## Common Stripe Test Cards

- **Success**: `4242 4242 4242 4242`
- **Decline**: `4000 0000 0000 0002`
- **Requires Authentication**: `4000 0025 0000 3155`
- **3D Secure**: `4000 0027 6000 3184`

## Resources

- [Laravel Cashier Documentation](https://laravel.com/docs/cashier)
- [Stripe API Documentation](https://stripe.com/docs/api)
- [Stripe Testing Guide](https://stripe.com/docs/testing)
- [Stripe Dashboard](https://dashboard.stripe.com)

