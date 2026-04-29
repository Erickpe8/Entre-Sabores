<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePostCommentRequest extends FormRequest
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
        $post = $this->route('post');
        if ($post === null) {
            return [
                'body' => ['required', 'string', 'max:2000'],
                'parent_id' => ['nullable', 'integer'],
            ];
        }

        return [
            'body' => ['required', 'string', 'max:2000'],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('comments', 'id')->where(fn ($q) => $q->where('post_id', $post->id)),
            ],
        ];
    }
}
