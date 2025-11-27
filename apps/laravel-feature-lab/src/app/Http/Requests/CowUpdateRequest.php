<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CowUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Adjust authorization as needed.
        return true;
    }

    public function rules(): array
    {
        $cowId = $this->route('cow') ? $this->route('cow')->id : null;

        return [
            'name' => 'required|string|max:255',
            'tag_number' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('cows', 'tag_number')->ignore($cowId),
            ],
            'breed' => 'nullable|string|max:255',
            'dob' => 'nullable|date',
            'weight_kg' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'meta' => 'nullable|array',
        ];
    }
}
