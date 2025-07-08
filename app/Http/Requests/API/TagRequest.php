<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TagRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'description' => 'nullable|string|max:255',
        ];

        // For store method, name is required and unique
        if ($this->isMethod('post')) {
            $rules['name'] = 'required|string|max:255|unique:tags,name';
        }

        // For update method, name is unique except for the current tag
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['name'] = [
                'required',
                'string',
                'max:255',
                Rule::unique('tags', 'name')->ignore($this->route('tag')),
            ];
        }

        return $rules;
    }
}
