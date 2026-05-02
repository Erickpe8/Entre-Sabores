<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserPostsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Alineado con `FilterPostsRequest` (máx. 30 ítems por página).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:30'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
