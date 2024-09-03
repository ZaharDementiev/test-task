<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['string', 'min:1', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['bool'],
        ];
    }
}
