<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CowTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_cow()
    {
        $payload = [
            'name' => 'Bessie',
            'tag_number' => 'A123',
        ];

        $response = $this->postJson('/api/cows', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Bessie')
            ->assertJsonPath('data.tag_number', 'A123');
    }
}
