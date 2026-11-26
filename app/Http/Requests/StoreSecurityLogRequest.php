<?php

namespace App\Http\Requests;

use App\Services\SecurityLogService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSecurityLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'event_type' => ['required', 'string', Rule::in(SecurityLogService::ALLOWED_CLIENT_EVENTS)],
            'details' => ['nullable', 'array'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'event_type.required' => 'Jenis event keamanan wajib diisi.',
            'event_type.in' => 'Jenis event keamanan tidak dikenali.',
        ];
    }
}
