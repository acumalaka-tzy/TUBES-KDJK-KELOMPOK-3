<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KickParticipantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'target_user_id' => ['required', 'string', 'max:64'],
        ];
    }

    public function messages(): array
    {
        return [
            'target_user_id.required' => 'Target User ID wajib diisi.',
        ];
    }
}
