<?php

namespace Tests\Unit\Resources;

use App\Http\Resources\CowResource;
use App\Models\Cow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CowResourceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: CowResource transforms cow to array
     */
    public function test_cow_resource_to_array(): void
    {
        $cow = Cow::factory()->create([
            'name' => 'Bessie',
            'tag_number' => 'COW-001',
            'breed' => 'Holstein',
            'dob' => '2021-06-01',
            'weight_kg' => 450.25,
            'notes' => 'Test notes',
            'meta' => ['key' => 'value'],
        ]);

        $resource = new CowResource($cow);
        $array = $resource->toArray(request());

        $this->assertEquals($cow->id, $array['id']);
        $this->assertEquals('Bessie', $array['name']);
        $this->assertEquals('COW-001', $array['tag_number']);
        $this->assertEquals('Holstein', $array['breed']);
        $this->assertEquals('2021-06-01', $array['dob']);
        $this->assertEquals(450.25, $array['weight_kg']);
        $this->assertEquals('Test notes', $array['notes']);
        $this->assertEquals(['key' => 'value'], $array['meta']);
        $this->assertArrayHasKey('created_at', $array);
        $this->assertArrayHasKey('updated_at', $array);
    }

    /**
     * Test: CowResource handles null values
     */
    public function test_cow_resource_handles_null_values(): void
    {
        $cow = Cow::factory()->create([
            'name' => 'Bessie',
            'tag_number' => null,
            'breed' => null,
            'dob' => null,
            'weight_kg' => null,
            'notes' => null,
            'meta' => null,
        ]);

        $resource = new CowResource($cow);
        $array = $resource->toArray(request());

        $this->assertNull($array['tag_number']);
        $this->assertNull($array['breed']);
        $this->assertNull($array['dob']);
        $this->assertNull($array['weight_kg']);
        $this->assertNull($array['notes']);
        $this->assertNull($array['meta']);
    }

    /**
     * Test: CowResource formats dates correctly
     */
    public function test_cow_resource_formats_dates(): void
    {
        $cow = Cow::factory()->create([
            'dob' => '2021-06-01',
        ]);

        $resource = new CowResource($cow);
        $array = $resource->toArray(request());

        $this->assertEquals('2021-06-01', $array['dob']);
        $this->assertIsString($array['created_at']);
        $this->assertIsString($array['updated_at']);
    }

    /**
     * Test: CowResource formats weight_kg as float
     */
    public function test_cow_resource_formats_weight_as_float(): void
    {
        $cow = Cow::factory()->create([
            'weight_kg' => 450.25,
        ]);

        $resource = new CowResource($cow);
        $array = $resource->toArray(request());

        $this->assertIsFloat($array['weight_kg']);
        $this->assertEquals(450.25, $array['weight_kg']);
    }

    /**
     * Test: CowResource handles meta array
     */
    public function test_cow_resource_handles_meta_array(): void
    {
        $cow = Cow::factory()->create([
            'meta' => ['key1' => 'value1', 'key2' => 'value2'],
        ]);

        $resource = new CowResource($cow);
        $array = $resource->toArray(request());

        $this->assertIsArray($array['meta']);
        $this->assertEquals('value1', $array['meta']['key1']);
        $this->assertEquals('value2', $array['meta']['key2']);
    }
}



