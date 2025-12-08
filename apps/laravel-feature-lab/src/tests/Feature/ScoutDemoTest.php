<?php

namespace Tests\Feature;

use App\Models\Cow;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScoutDemoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Use collection driver for testing (no external service needed)
        config(['scout.driver' => 'collection']);
    }

    /**
     * Test: POST api/scout-demo/search
     * Demonstrates: Basic search endpoint
     */
    public function test_basic_search_endpoint(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        // Create some cows for searching
        Cow::factory()->create(['name' => 'Bessie']);
        Cow::factory()->create(['name' => 'Daisy']);
        Cow::makeAllSearchable();

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/scout-demo/search', [
                'q' => 'Bessie',
                'model' => 'cows',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'query',
                'model',
                'results',
                'count',
            ])
            ->assertJson([
                'query' => 'Bessie',
                'model' => 'cows',
            ]);
    }

    /**
     * Test: POST api/scout-demo/search validation
     */
    public function test_basic_search_validation(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/scout-demo/search', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['q', 'model']);
    }

    /**
     * Test: POST api/scout-demo/search/paginated
     * Demonstrates: Paginated search endpoint
     */
    public function test_paginated_search_endpoint(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        Cow::factory()->count(20)->create();
        Cow::makeAllSearchable();

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/scout-demo/search/paginated', [
                'q' => 'cow',
                'model' => 'cows',
                'per_page' => 10,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'query',
                'model',
                'data',
                'pagination' => [
                    'current_page',
                    'per_page',
                    'total',
                    'last_page',
                ],
            ])
            ->assertJson([
                'query' => 'cow',
                'model' => 'cows',
            ]);
    }

    /**
     * Test: POST api/scout-demo/search/filtered
     * Demonstrates: Filtered search endpoint
     */
    public function test_filtered_search_endpoint(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        Cow::factory()->create(['name' => 'Bessie', 'breed' => 'Holstein']);
        Cow::makeAllSearchable();

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/scout-demo/search/filtered', [
                'q' => 'Bessie',
                'model' => 'cows',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'query',
                'model',
                'results',
                'count',
                'note',
            ]);
    }

    /**
     * Test: POST api/scout-demo/search/field
     * Demonstrates: Field-specific search endpoint
     */
    public function test_field_search_endpoint(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        Cow::factory()->create(['name' => 'Bessie']);
        Cow::makeAllSearchable();

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/scout-demo/search/field', [
                'q' => 'Bessie',
                'model' => 'cows',
                'field' => 'name',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'query',
                'model',
                'field',
                'results',
                'count',
                'note',
            ]);
    }

    /**
     * Test: POST api/scout-demo/search/ordered
     * Demonstrates: Ordered search endpoint
     */
    public function test_ordered_search_endpoint(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        Cow::factory()->count(5)->create();
        Cow::makeAllSearchable();

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/scout-demo/search/ordered', [
                'q' => 'cow',
                'model' => 'cows',
                'order_by' => 'name',
                'order_direction' => 'asc',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'query',
                'model',
                'order_by',
                'order_direction',
                'results',
                'count',
            ])
            ->assertJson([
                'order_by' => 'name',
                'order_direction' => 'asc',
            ]);
    }

    /**
     * Test: POST api/scout-demo/import
     * Demonstrates: Bulk import endpoint
     */
    public function test_import_all_endpoint(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        Cow::factory()->count(5)->create();

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/scout-demo/import', [
                'model' => 'cows',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'model',
            ])
            ->assertJson([
                'model' => 'cows',
            ]);
    }

    /**
     * Test: POST api/scout-demo/import validation
     */
    public function test_import_all_validation(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/scout-demo/import', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['model']);
    }

    /**
     * Test: POST api/scout-demo/remove
     * Demonstrates: Remove all from index endpoint
     */
    public function test_remove_all_endpoint(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        Cow::factory()->count(5)->create();
        Cow::makeAllSearchable();

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/scout-demo/remove', [
                'model' => 'cows',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'model',
            ])
            ->assertJson([
                'model' => 'cows',
            ]);
    }

    /**
     * Test: GET api/scout-demo/stats
     * Demonstrates: Search statistics endpoint
     */
    public function test_stats_endpoint(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/scout-demo/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'driver',
                'prefix',
                'queue',
                'meilisearch_host',
                'note',
            ]);
    }

    /**
     * Test: All endpoints require authentication
     */
    public function test_endpoints_require_authentication(): void
    {
        $this->postJson('/api/scout-demo/search')->assertStatus(401);
        $this->postJson('/api/scout-demo/search/paginated')->assertStatus(401);
        $this->postJson('/api/scout-demo/search/filtered')->assertStatus(401);
        $this->postJson('/api/scout-demo/search/field')->assertStatus(401);
        $this->postJson('/api/scout-demo/search/ordered')->assertStatus(401);
        $this->postJson('/api/scout-demo/import')->assertStatus(401);
        $this->postJson('/api/scout-demo/remove')->assertStatus(401);
        $this->getJson('/api/scout-demo/stats')->assertStatus(401);
    }
}


