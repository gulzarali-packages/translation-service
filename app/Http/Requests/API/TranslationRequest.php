<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TranslationRequest extends FormRequest
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
            'language_id' => 'required|exists:languages,id',
            'key' => 'required|string|max:255',
            'content' => 'required|string',
            'metadata' => 'nullable|array',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
        ];

        // For store method, ensure the combination of language_id and key is unique
        if ($this->isMethod('post')) {
            $rules['key'] = [
                'required',
                'string',
                'max:255',
                Rule::unique('translations')->where(function ($query) {
                    return $query->where('language_id', $this->language_id);
                }),
            ];
        }

        // For update method, ensure the combination is unique except for the current translation
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['key'] = [
                'required',
                'string',
                'max:255',
                Rule::unique('translations')->where(function ($query) {
                    return $query->where('language_id', $this->language_id);
                })->ignore($this->route('translation')),
            ];
        }

        return $rules;
    }
}
