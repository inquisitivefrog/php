<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: POST api/email/verification-notification
     * Demonstrates: Request email verification via API
     */
    public function test_email_verification_notification_can_be_requested_via_api(): void
    {
        $user = User::factory()->unverified()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/email/verification-notification');

        // Laravel returns 200 for email verification notification, not 202
        $response->assertStatus(200);
    }

    /**
     * Test: POST api/email/verification-notification requires authentication
     */
    public function test_email_verification_notification_requires_authentication(): void
    {
        $response = $this->postJson('/api/email/verification-notification');
        $response->assertStatus(401);
    }

    /**
     * Test: GET api/verify-email/{id}/{hash}
     * Demonstrates: Verify email via API
     */
    public function test_email_can_be_verified_via_api(): void
    {
        $user = User::factory()->unverified()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // Extract the path from the full URL
        $path = parse_url($verificationUrl, PHP_URL_PATH);
        $query = parse_url($verificationUrl, PHP_URL_QUERY);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson($path . '?' . $query);

        // Email verification redirects (302) for web routes, but we can check the event and user state
        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        // Accept either 200 or 302 (redirect) status
        $this->assertContains($response->status(), [200, 302]);
    }

    /**
     * Test: GET api/verify-email/{id}/{hash} with invalid hash
     */
    public function test_email_is_not_verified_with_invalid_hash_via_api(): void
    {
        $user = User::factory()->unverified()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('wrong-email')]
        );

        $path = parse_url($verificationUrl, PHP_URL_PATH);
        $query = parse_url($verificationUrl, PHP_URL_QUERY);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson($path . '?' . $query);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }
}

