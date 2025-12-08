<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Cashier\Subscription;
use Laravel\Pennant\Feature;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that user can check subscription status
     */
    public function test_user_can_check_subscription_status(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/subscription');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'has_subscription',
                'subscriptions',
                'on_trial',
                'on_generic_trial',
            ])
            ->assertJson([
                'has_subscription' => false,
                'on_trial' => false,
                'on_generic_trial' => false,
            ]);
    }

    /**
     * Test that subscription feature flags work correctly
     */
    public function test_subscription_feature_flags(): void
    {
        $user = User::factory()->create();
        $user->refresh();

        // User without subscription should not have premium features
        $this->assertFalse(Feature::for($user)->active('premium-analytics'));
        $this->assertFalse(Feature::for($user)->active('team-collaboration'));
        $this->assertFalse(Feature::for($user)->active('unlimited-tasks'));

        // Note: To test with actual subscriptions, you would need to:
        // 1. Create a subscription in the database
        // 2. Or mock the subscription methods
        // For now, we test that the feature flags are properly defined
    }

    /**
     * Test that subscription requires authentication
     */
    public function test_subscription_routes_require_authentication(): void
    {
        $response = $this->getJson('/api/subscription');
        $response->assertStatus(401);
    }

    /**
     * Test checkout endpoint requires price_id
     */
    public function test_checkout_requires_price_id(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/subscription/checkout', []);

        $response->assertStatus(422)
            ->assertJson([
                'error' => 'Price ID is required',
            ]);
    }

    /**
     * Test cancel endpoint requires active subscription
     */
    public function test_cancel_requires_active_subscription(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/subscription/cancel');

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'No active subscription found',
            ]);
    }

    /**
     * Test: GET api/subscription/portal
     * Demonstrates: Billing portal endpoint
     */
    public function test_portal_endpoint_requires_authentication(): void
    {
        $response = $this->getJson('/api/subscription/portal');
        $response->assertStatus(401);
    }

    /**
     * Test: POST api/subscription/resume
     * Demonstrates: Resume subscription endpoint
     */
    public function test_resume_requires_cancelled_subscription(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/subscription/resume');

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'No subscription found',
            ]);
    }

    /**
     * Test: POST api/subscription/resume requires authentication
     */
    public function test_resume_requires_authentication(): void
    {
        $response = $this->postJson('/api/subscription/resume');
        $response->assertStatus(401);
    }
}

