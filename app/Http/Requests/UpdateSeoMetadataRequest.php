<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSeoMetadataRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'meta_title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'meta_description' => [
                'nullable',
                'string',
                'max:500',
            ],

            'meta_keywords' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'canonical_url' => [
                'nullable',
                'url',
                'max:2048',
            ],

            'og_title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'og_description' => [
                'nullable',
                'string',
                'max:500',
            ],

            'og_image' => [
                'nullable',
                'string',
                'max:2048',
            ],
        ];
    }
}
