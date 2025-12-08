<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\CowStoreRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CowStoreRequestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: CowStoreRequest validation rules
     */
    public function test_cow_store_request_validation_rules(): void
    {
        $request = new CowStoreRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('tag_number', $rules);
        $this->assertArrayHasKey('breed', $rules);
        $this->assertArrayHasKey('dob', $rules);
        $this->assertArrayHasKey('weight_kg', $rules);
        $this->assertArrayHasKey('notes', $rules);
        $this->assertArrayHasKey('meta', $rules);
    }

    /**
     * Test: CowStoreRequest name is required
     */
    public function test_cow_store_request_name_is_required(): void
    {
        $request = new CowStoreRequest();
        $rules = $request->rules();

        $validator = Validator::make([], $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    /**
     * Test: CowStoreRequest name must be string
     */
    public function test_cow_store_request_name_must_be_string(): void
    {
        $request = new CowStoreRequest();
        $rules = $request->rules();

        $validator = Validator::make(['name' => 123], $rules);
        $this->assertTrue($validator->fails());
    }

    /**
     * Test: CowStoreRequest name max length
     */
    public function test_cow_store_request_name_max_length(): void
    {
        $request = new CowStoreRequest();
        $rules = $request->rules();

        $validator = Validator::make(['name' => str_repeat('a', 256)], $rules);
        $this->assertTrue($validator->fails());
    }

    /**
     * Test: CowStoreRequest tag_number must be unique
     */
    public function test_cow_store_request_tag_number_must_be_unique(): void
    {
        \App\Models\Cow::factory()->create(['tag_number' => 'COW-001']);
        
        $request = new CowStoreRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => 'Bessie',
            'tag_number' => 'COW-001',
        ], $rules);
        
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('tag_number', $validator->errors()->toArray());
    }

    /**
     * Test: CowStoreRequest weight_kg must be numeric
     */
    public function test_cow_store_request_weight_kg_must_be_numeric(): void
    {
        $request = new CowStoreRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => 'Bessie',
            'weight_kg' => 'not-a-number',
        ], $rules);
        
        $this->assertTrue($validator->fails());
    }

    /**
     * Test: CowStoreRequest weight_kg must be positive
     */
    public function test_cow_store_request_weight_kg_must_be_positive(): void
    {
        $request = new CowStoreRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => 'Bessie',
            'weight_kg' => -10,
        ], $rules);
        
        $this->assertTrue($validator->fails());
    }

    /**
     * Test: CowStoreRequest dob must be valid date
     */
    public function test_cow_store_request_dob_must_be_valid_date(): void
    {
        $request = new CowStoreRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => 'Bessie',
            'dob' => 'invalid-date',
        ], $rules);
        
        $this->assertTrue($validator->fails());
    }

    /**
     * Test: CowStoreRequest meta must be array
     */
    public function test_cow_store_request_meta_must_be_array(): void
    {
        $request = new CowStoreRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => 'Bessie',
            'meta' => 'not-an-array',
        ], $rules);
        
        $this->assertTrue($validator->fails());
    }

    /**
     * Test: CowStoreRequest valid data passes validation
     */
    public function test_cow_store_request_valid_data_passes(): void
    {
        $request = new CowStoreRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => 'Bessie',
            'tag_number' => 'COW-001',
            'breed' => 'Holstein',
            'dob' => '2021-06-01',
            'weight_kg' => 450.25,
            'notes' => 'Test notes',
            'meta' => ['key' => 'value'],
        ], $rules);
        
        $this->assertFalse($validator->fails());
    }

    /**
     * Test: CowStoreRequest authorize returns true
     */
    public function test_cow_store_request_authorize(): void
    {
        $request = new CowStoreRequest();
        $this->assertTrue($request->authorize());
    }
}


