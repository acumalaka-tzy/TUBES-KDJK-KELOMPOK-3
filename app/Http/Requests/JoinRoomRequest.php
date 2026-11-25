<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JoinRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'room_id' => ['required', 'string', 'min:4', 'max:64'],
            'pin' => ['nullable', 'string', 'max:16'],
            'invite_token' => ['nullable', 'string', 'max:128'],
        ];
    }

    public function messages(): array
    {
        return [
            'room_id.required' => 'Room ID wajib diisi.',
        ];
    }
}
