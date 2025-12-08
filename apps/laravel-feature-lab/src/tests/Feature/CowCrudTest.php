<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CowCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_read_update_delete_cow()
    {
        // Authenticate regular user for create/read
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        // Create
        $payload = [
            'name' => 'Bessie',
            'tag_number' => '12345',
            'breed' => 'Jersey',
            'dob' => '2021-06-01',
            'weight_kg' => 450.25,
        ];

        $createResp = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/cows', $payload);
        $createResp->assertStatus(201);
        $cowId = $createResp->json('data.id');

        // Read
        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/cows/{$cowId}")
            ->assertStatus(200)
            ->assertJsonPath('data.name', 'Bessie');

        // Update requires admin user (per CowPolicy)
        // Policy checks for email ending in @admin.example.com or containing 'admin@'
        // Update the existing user to be an admin instead of creating a new one
        $user->email = 'admin@example.com'; // Contains 'admin@' pattern
        $user->save();
        $user->refresh();
        
        // Create a new token for the updated user
        $adminToken = $user->createToken('admin-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$adminToken}")
            ->putJson("/api/cows/{$cowId}", ['name' => 'Bessie II']);
        
        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Bessie II');

        // Delete requires admin user (per CowPolicy)
        $this->withHeader('Authorization', "Bearer {$adminToken}")
            ->deleteJson("/api/cows/{$cowId}")
            ->assertStatus(204);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/cows/{$cowId}")
            ->assertStatus(404);
    }
}
