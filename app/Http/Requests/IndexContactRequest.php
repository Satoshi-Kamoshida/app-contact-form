<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'keyword' => ['nullable', 'string'],
            'gender' => ['nullable', 'integer'],
            'category_id' => ['nullable', 'integer'],
            'date' => ['nullable', 'date'],
        ];
    }
}
