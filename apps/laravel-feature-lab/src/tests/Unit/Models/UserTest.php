<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: User model fillable attributes
     */
    public function test_user_has_correct_fillable_attributes(): void
    {
        $user = new User();
        $fillable = $user->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('email', $fillable);
        $this->assertContains('password', $fillable);
    }

    /**
     * Test: User model hidden attributes
     */
    public function test_user_has_correct_hidden_attributes(): void
    {
        $user = new User();
        $hidden = $user->getHidden();

        $this->assertContains('password', $hidden);
        $this->assertContains('remember_token', $hidden);
    }

    /**
     * Test: User model casts
     */
    public function test_user_has_correct_casts(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'trial_ends_at' => now()->addDays(7),
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $user->email_verified_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $user->trial_ends_at);
    }

    /**
     * Test: User password is hashed
     */
    public function test_user_password_is_hashed(): void
    {
        $user = User::factory()->create([
            'password' => 'plain-password',
        ]);

        $this->assertNotEquals('plain-password', $user->password);
        $this->assertTrue(\Hash::check('plain-password', $user->password));
    }

    /**
     * Test: User searchable array
     */
    public function test_user_to_searchable_array(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $searchable = $user->toSearchableArray();

        $this->assertEquals($user->id, $searchable['id']);
        $this->assertEquals('John Doe', $searchable['name']);
        $this->assertEquals('john@example.com', $searchable['email']);
    }

    /**
     * Test: User searchable index name
     */
    public function test_user_searchable_as(): void
    {
        $user = new User();
        $this->assertEquals('users', $user->searchableAs());
    }

    /**
     * Test: User can create API tokens
     */
    public function test_user_can_create_api_tokens(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token');

        $this->assertNotNull($token);
        $this->assertNotNull($token->plainTextToken);
        $this->assertEquals('test-token', $token->accessToken->name);
    }

    /**
     * Test: User has Billable trait
     */
    public function test_user_has_billable_trait(): void
    {
        $user = User::factory()->create();
        
        // Check if user has Cashier methods
        $this->assertTrue(method_exists($user, 'subscriptions'));
        $this->assertTrue(method_exists($user, 'subscribed'));
    }
}



