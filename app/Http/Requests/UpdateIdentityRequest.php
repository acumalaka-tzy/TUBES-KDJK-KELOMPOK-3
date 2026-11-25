<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIdentityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'display_name' => ['nullable', 'string', 'min:2', 'max:50'],
            'avatar_color' => ['nullable', 'string', 'regex:/^#[a-fA-F0-9]{6}$/'],
            'preferences' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'display_name.min' => 'Display Name minimal 2 karakter.',
            'display_name.max' => 'Display Name maksimal 50 karakter.',
            'avatar_color.regex' => 'Format warna avatar harus hex yang valid.',
        ];
    }
}
