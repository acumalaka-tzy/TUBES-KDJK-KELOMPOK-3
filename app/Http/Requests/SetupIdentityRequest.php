<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SetupIdentityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'display_name' => ['required', 'string', 'min:2', 'max:50'],
            'user_id' => ['nullable', 'string', 'max:64', 'regex:/^[a-zA-Z0-9_\-]+$/'],
            'avatar_color' => ['nullable', 'string', 'regex:/^#[a-fA-F0-9]{6}$/'],
            'preferences' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'display_name.required' => 'Display Name wajib diisi.',
            'display_name.min' => 'Display Name minimal 2 karakter.',
            'display_name.max' => 'Display Name maksimal 50 karakter.',
            'avatar_color.regex' => 'Format warna avatar harus hex yang valid (contoh: #2563eb).',
        ];
    }
}
