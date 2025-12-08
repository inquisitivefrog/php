<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CowTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_cow()
    {
        // Authenticate user
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $payload = [
            'name' => 'Bessie',
            'tag_number' => 'A123',
        ];

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/cows', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Bessie')
            ->assertJsonPath('data.tag_number', 'A123');
    }
}
