<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LanguageRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ];

        // For store method, code is required and unique
        if ($this->isMethod('post')) {
            $rules['code'] = 'required|string|max:10|unique:languages,code';
        }

        // For update method, code is unique except for the current language
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['code'] = [
                'required',
                'string',
                'max:10',
                Rule::unique('languages', 'code')->ignore($this->route('language')),
            ];
        }

        return $rules;
    }
}
