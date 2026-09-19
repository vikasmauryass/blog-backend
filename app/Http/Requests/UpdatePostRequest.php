<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class UpdatePostRequest extends FormRequest
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
        $post = $this->route('post');

        return [
            'category_id' => [
                'required',
                'integer',
                'exists:categories,id',
            ],

            'subcategory_id' => [
                'nullable',
                'integer',
                'exists:subcategories,id',
            ],

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('posts', 'slug')
                    ->ignore($post->id),
            ],

            'excerpt' => [
                'nullable',
                'string',
                'max:500',
            ],

            'content' => [
                'required',
                'string',
            ],

            'content_type' => [
                'required',
                Rule::in([
                    'article',
                    'video',
                    'mixed',
                ]),
            ],

            'status' => [
                'required',
                Rule::in([
                    'draft',
                    'published',
                ]),
            ],

            'published_at' => [
                'nullable',
                'date',
            ],

            'tags' => [
                'nullable',
                'array',
            ],

            'tags.*' => [
                'integer',
                'exists:tags,id',
            ],
        ];
    }
}
