<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'room_name' => ['required', 'string', 'min:2', 'max:100'],
            'room_id' => ['nullable', 'string', 'min:4', 'max:64', 'regex:/^[a-zA-Z0-9_\-]+$/', 'unique:rooms,room_id'],
            'id_format' => ['nullable', 'string', 'in:code,numeric,slug'],
            'description' => ['nullable', 'string', 'max:500'],
            'pin' => ['nullable', 'string', 'min:4', 'max:16'],
            'pin_expires_in_minutes' => ['nullable', 'integer', 'min:1', 'max:10080'],
            'dynamic_pin_enabled' => ['nullable', 'boolean'],
            'max_participants' => ['nullable', 'integer', 'min:2', 'max:50'],
            'e2ee_enabled' => ['nullable', 'boolean'],
            'ephemeral_enabled' => ['nullable', 'boolean'],
            'default_ephemeral_duration' => ['nullable', 'integer', 'in:0,10,30,60,300'],
            'allow_invites' => ['nullable', 'boolean'],
            'allow_reactions' => ['nullable', 'boolean'],
            'allow_replies' => ['nullable', 'boolean'],
            'expires_in_hours' => ['nullable', 'integer', 'min:1', 'max:168'],
        ];
    }

    public function messages(): array
    {
        return [
            'room_name.required' => 'Nama room wajib diisi.',
            'room_name.min' => 'Nama room minimal 2 karakter.',
            'room_name.max' => 'Nama room maksimal 100 karakter.',
            'room_id.unique' => 'Room ID tersebut sudah digunakan. Pilih ID lain atau biarkan sistem membuatnya otomatis.',
            'pin.min' => 'PIN minimal 4 karakter.',
        ];
    }
}
