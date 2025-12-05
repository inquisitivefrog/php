<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Cashier\Subscription;

/**
 * Subscription controller demonstrating Cashier functionality
 */
class SubscriptionController extends Controller
{
    /**
     * Get the current user's subscription status
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'has_subscription' => $user->subscribed(),
            'subscriptions' => $user->subscriptions->map(function (Subscription $subscription) {
                return [
                    'id' => $subscription->id,
                    'name' => $subscription->name,
                    'stripe_id' => $subscription->stripe_id,
                    'stripe_status' => $subscription->stripe_status,
                    'stripe_price' => $subscription->stripe_price,
                    'quantity' => $subscription->quantity,
                    'trial_ends_at' => $subscription->trial_ends_at?->toIso8601String(),
                    'ends_at' => $subscription->ends_at?->toIso8601String(),
                    'created_at' => $subscription->created_at->toIso8601String(),
                ];
            }),
            'on_trial' => $user->onTrial(),
            'on_generic_trial' => $user->onGenericTrial(),
        ]);
    }

    /**
     * Create a checkout session for subscribing
     * 
     * This would typically redirect to Stripe Checkout
     */
    public function checkout(Request $request): JsonResponse
    {
        $user = $request->user();
        $priceId = $request->input('price_id');

        if (!$priceId) {
            return response()->json([
                'error' => 'Price ID is required',
            ], 422);
        }

        try {
            // Create a Stripe Checkout session
            $checkout = $user->checkout([$priceId], [
                'success_url' => route('subscription.success'),
                'cancel_url' => route('subscription.cancel'),
                'mode' => 'subscription',
            ]);

            return response()->json([
                'checkout_url' => $checkout->url,
                'session_id' => $checkout->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create checkout session',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel the user's subscription
     */
    public function cancel(Request $request): JsonResponse
    {
        $user = $request->user();
        $subscriptionName = $request->input('subscription', 'default');

        if (!$user->subscribed($subscriptionName)) {
            return response()->json([
                'error' => 'No active subscription found',
            ], 404);
        }

        $subscription = $user->subscription($subscriptionName);
        $subscription->cancel();

        return response()->json([
            'message' => 'Subscription cancelled successfully',
            'subscription' => [
                'id' => $subscription->id,
                'name' => $subscription->name,
                'stripe_status' => $subscription->stripe_status,
                'ends_at' => $subscription->ends_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Resume a cancelled subscription
     */
    public function resume(Request $request): JsonResponse
    {
        $user = $request->user();
        $subscriptionName = $request->input('subscription', 'default');

        if (!$user->subscription($subscriptionName)) {
            return response()->json([
                'error' => 'No subscription found',
            ], 404);
        }

        $subscription = $user->subscription($subscriptionName);
        
        if (!$subscription->cancelled()) {
            return response()->json([
                'error' => 'Subscription is not cancelled',
            ], 422);
        }

        $subscription->resume();

        return response()->json([
            'message' => 'Subscription resumed successfully',
            'subscription' => [
                'id' => $subscription->id,
                'name' => $subscription->name,
                'stripe_status' => $subscription->stripe_status,
            ],
        ]);
    }

    /**
     * Get the billing portal URL
     */
    public function portal(Request $request): JsonResponse
    {
        $user = $request->user();

        try {
            $portal = $user->billingPortalUrl(route('subscription.index'));

            return response()->json([
                'portal_url' => $portal,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create billing portal session',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}

