<?php

namespace App\Http\Requests\Shop;

use Illuminate\Foundation\Http\FormRequest;

class ChatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'min:1', 'max:2000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $message = preg_replace('/\s+/u', ' ', trim((string) $this->input('message')));

        $this->merge(['message' => $message]);
    }
}
