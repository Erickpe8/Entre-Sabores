<?php

namespace App\Http\Requests;

use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterPostsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'following' => ['sometimes', 'boolean'],
            'experience_type' => ['nullable', 'string', Rule::in(Post::EXPERIENCE_TYPES)],
            'drink_type' => ['nullable', 'string', Rule::in(Post::DRINK_TYPES)],
            'sort' => ['nullable', 'string', Rule::in(['recent', 'popular'])],
        ];
    }
}
