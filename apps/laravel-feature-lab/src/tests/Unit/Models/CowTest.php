<?php

namespace Tests\Unit\Models;

use App\Models\Cow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Cow model fillable attributes
     */
    public function test_cow_has_correct_fillable_attributes(): void
    {
        $cow = new Cow();
        $fillable = $cow->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('tag_number', $fillable);
        $this->assertContains('breed', $fillable);
        $this->assertContains('dob', $fillable);
        $this->assertContains('weight_kg', $fillable);
        $this->assertContains('notes', $fillable);
        $this->assertContains('meta', $fillable);
    }

    /**
     * Test: Cow model casts
     */
    public function test_cow_has_correct_casts(): void
    {
        $cow = Cow::factory()->create([
            'dob' => '2021-06-01',
            'weight_kg' => 450.25,
            'meta' => ['key' => 'value'],
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $cow->dob);
        $this->assertEquals('450.25', (string) $cow->weight_kg);
        $this->assertIsArray($cow->meta);
        $this->assertEquals('value', $cow->meta['key']);
    }

    /**
     * Test: Cow searchable array
     */
    public function test_cow_to_searchable_array(): void
    {
        $cow = Cow::factory()->create([
            'name' => 'Bessie',
            'tag_number' => 'COW-001',
            'breed' => 'Holstein',
            'dob' => '2021-06-01',
            'weight_kg' => 450.25,
            'notes' => 'Test notes',
        ]);

        $searchable = $cow->toSearchableArray();

        $this->assertEquals($cow->id, $searchable['id']);
        $this->assertEquals('Bessie', $searchable['name']);
        $this->assertEquals('COW-001', $searchable['tag_number']);
        $this->assertEquals('Holstein', $searchable['breed']);
        $this->assertEquals('2021-06-01', $searchable['dob']);
        $this->assertEquals('450.25', (string) $searchable['weight_kg']);
        $this->assertEquals('Test notes', $searchable['notes']);
    }

    /**
     * Test: Cow searchable array with null dob
     */
    public function test_cow_to_searchable_array_with_null_dob(): void
    {
        $cow = Cow::factory()->create([
            'dob' => null,
        ]);

        $searchable = $cow->toSearchableArray();
        $this->assertNull($searchable['dob']);
    }

    /**
     * Test: Cow searchable index name
     */
    public function test_cow_searchable_as(): void
    {
        $cow = new Cow();
        $this->assertEquals('cows', $cow->searchableAs());
    }

    /**
     * Test: Cow can be created with all attributes
     */
    public function test_cow_can_be_created_with_all_attributes(): void
    {
        $cow = Cow::create([
            'name' => 'Bessie',
            'tag_number' => 'COW-001',
            'breed' => 'Holstein',
            'dob' => '2021-06-01',
            'weight_kg' => 450.25,
            'notes' => 'Test notes',
            'meta' => ['key' => 'value'],
        ]);

        $this->assertDatabaseHas('cows', [
            'id' => $cow->id,
            'name' => 'Bessie',
            'tag_number' => 'COW-001',
        ]);
    }
}



