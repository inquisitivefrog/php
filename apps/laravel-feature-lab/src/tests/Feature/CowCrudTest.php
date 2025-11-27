<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CowCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_read_update_delete_cow()
    {
        // Create
        $payload = [
            'name' => 'Bessie',
            'tag_number' => '12345',
            'breed' => 'Jersey',
            'dob' => '2021-06-01',
            'weight_kg' => 450.25,
        ];

        $createResp = $this->postJson('/api/cows', $payload);
        $createResp->assertStatus(201);
        $cowId = $createResp->json('data.id');

        // Read
        $this->getJson("/api/cows/{$cowId}")
            ->assertStatus(200)
            ->assertJsonPath('data.name', 'Bessie');

        // Update
        $this->putJson("/api/cows/{$cowId}", ['name' => 'Bessie II'])
            ->assertStatus(200)
            ->assertJsonPath('data.name', 'Bessie II');

        // Delete
        $this->deleteJson("/api/cows/{$cowId}")
            ->assertStatus(204);

        $this->getJson("/api/cows/{$cowId}")->assertStatus(404);
    }
}
