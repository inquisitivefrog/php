<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class QueueDemoTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: POST api/queue/test
     * Demonstrates: Basic job dispatching endpoint
     */
    public function test_dispatch_test_job_endpoint(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/queue/test');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'job',
            ]);

        Queue::assertPushed(\App\Jobs\TestJob::class);
    }

    /**
     * Test: POST api/queue/email
     * Demonstrates: Email job dispatching endpoint
     */
    public function test_dispatch_email_job_endpoint(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/queue/email', [
                'to' => 'test@example.com',
                'subject' => 'Test Subject',
                'body' => 'Test Body',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'queue',
            ])
            ->assertJson([
                'queue' => 'emails',
            ]);

        Queue::assertPushed(\App\Jobs\ProcessEmailJob::class);
    }

    /**
     * Test: POST api/queue/email validation
     */
    public function test_dispatch_email_job_validation(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/queue/email', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['to', 'subject', 'body']);
    }

    /**
     * Test: POST api/queue/delayed
     * Demonstrates: Delayed job dispatching endpoint
     */
    public function test_dispatch_delayed_job_endpoint(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/queue/delayed', [
                'message' => 'Delayed message',
                'delay_seconds' => 60,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'delay_seconds',
            ])
            ->assertJson([
                'delay_seconds' => 60,
            ]);

        Queue::assertPushed(\App\Jobs\DelayedJob::class);
    }

    /**
     * Test: POST api/queue/delayed with default delay
     */
    public function test_dispatch_delayed_job_default_delay(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/queue/delayed', [
                'message' => 'Delayed message',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'delay_seconds' => 0,
            ]);
    }

    /**
     * Test: POST api/queue/chain
     * Demonstrates: Chained jobs endpoint
     */
    public function test_dispatch_chained_job_endpoint(): void
    {
        Bus::fake();

        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/queue/chain');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'steps',
            ])
            ->assertJson([
                'steps' => 3,
            ]);

        Bus::assertChained([
            \App\Jobs\ChainedJob::class,
            \App\Jobs\ChainedJob::class,
            \App\Jobs\ChainedJob::class,
        ]);
    }

    /**
     * Test: POST api/queue/batch
     * Demonstrates: Batch jobs endpoint
     */
    public function test_dispatch_batch_job_endpoint(): void
    {
        Bus::fake();

        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/queue/batch', [
                'items' => ['item1', 'item2', 'item3'],
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'batch_id',
                'job_count',
            ])
            ->assertJson([
                'job_count' => 3,
            ]);

        Bus::assertBatched(function ($batch) {
            return $batch->jobs->count() === 3;
        });
    }

    /**
     * Test: POST api/queue/batch validation
     */
    public function test_dispatch_batch_job_validation(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        // Empty items
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/queue/batch', [
                'items' => [],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items']);

        // Too many items
        $response2 = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/queue/batch', [
                'items' => array_fill(0, 11, 'item'),
            ]);

        $response2->assertStatus(422)
            ->assertJsonValidationErrors(['items']);
    }

    /**
     * Test: POST api/queue/failed
     * Demonstrates: Failed job endpoint
     */
    public function test_dispatch_failed_job_endpoint(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/queue/failed');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'tries',
                'backoff',
            ])
            ->assertJson([
                'tries' => 3,
                'backoff' => 10,
            ]);

        Queue::assertPushed(\App\Jobs\FailedJobExample::class);
    }

    /**
     * Test: GET api/queue/stats
     * Demonstrates: Queue statistics endpoint
     */
    public function test_queue_stats_endpoint(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/queue/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'note',
                'queues',
            ]);
        
        $queues = $response->json('queues');
        $this->assertArrayHasKey('default', $queues);
        $this->assertArrayHasKey('emails', $queues);
    }

    /**
     * Test: All endpoints require authentication
     */
    public function test_endpoints_require_authentication(): void
    {
        $this->postJson('/api/queue/test')->assertStatus(401);
        $this->postJson('/api/queue/email')->assertStatus(401);
        $this->postJson('/api/queue/delayed')->assertStatus(401);
        $this->postJson('/api/queue/chain')->assertStatus(401);
        $this->postJson('/api/queue/batch')->assertStatus(401);
        $this->postJson('/api/queue/failed')->assertStatus(401);
        $this->getJson('/api/queue/stats')->assertStatus(401);
    }
}

