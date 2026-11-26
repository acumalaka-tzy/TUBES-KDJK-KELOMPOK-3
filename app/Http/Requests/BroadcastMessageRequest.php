<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BroadcastMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'max:2000'],
            'ephemeral_duration' => ['nullable', 'integer', 'min:0', 'max:3600'],
            'reply_to' => ['nullable', 'array'],
        ];
    }
}
