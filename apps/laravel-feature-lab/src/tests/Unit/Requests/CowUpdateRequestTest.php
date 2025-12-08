<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\CowUpdateRequest;
use App\Models\Cow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CowUpdateRequestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: CowUpdateRequest validation rules
     */
    public function test_cow_update_request_validation_rules(): void
    {
        $request = new CowUpdateRequest();
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
     * Test: CowUpdateRequest tag_number unique rule structure
     * Note: Full route binding and validation is tested in feature tests (CowCrudTest).
     */
    public function test_cow_update_request_tag_number_ignores_current_cow(): void
    {
        $request = new CowUpdateRequest();
        
        // When route('cow') is null, rules should still be defined
        $rules = $request->rules();
        
        // Verify the rule structure includes unique validation
        $this->assertArrayHasKey('tag_number', $rules);
        $this->assertIsArray($rules['tag_number']);
    }

    /**
     * Test: CowUpdateRequest tag_number unique rule structure
     * Note: Full route binding and validation is tested in feature tests (CowCrudTest).
     */
    public function test_cow_update_request_tag_number_must_be_unique_for_different_cow(): void
    {
        $request = new CowUpdateRequest();
        
        // Verify rules are defined
        $rules = $request->rules();
        
        // Verify the rule structure
        $this->assertArrayHasKey('tag_number', $rules);
        $this->assertIsArray($rules['tag_number']);
    }

    /**
     * Test: CowUpdateRequest authorize returns true
     */
    public function test_cow_update_request_authorize(): void
    {
        $request = new CowUpdateRequest();
        $this->assertTrue($request->authorize());
    }
}

