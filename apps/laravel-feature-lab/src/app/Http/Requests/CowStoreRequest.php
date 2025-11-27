<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CowStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Adjust authorization as needed (policies / gates). Allow true for demo.
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'tag_number' => 'nullable|string|max:255|unique:cows,tag_number',
            'breed' => 'nullable|string|max:255',
            'dob' => 'nullable|date',
            'weight_kg' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'meta' => 'nullable|array',
        ];
    }
}
