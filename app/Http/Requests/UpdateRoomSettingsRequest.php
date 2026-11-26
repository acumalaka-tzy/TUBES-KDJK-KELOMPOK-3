<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoomSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'room_name' => ['nullable', 'string', 'min:2', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'pin' => ['nullable', 'string', 'min:4', 'max:16'],
            'pin_expires_in_minutes' => ['nullable', 'integer', 'min:1', 'max:10080'],
            'dynamic_pin_enabled' => ['nullable', 'boolean'],
            'allow_invites' => ['nullable', 'boolean'],
            'allow_reactions' => ['nullable', 'boolean'],
            'allow_replies' => ['nullable', 'boolean'],
            'default_ephemeral_duration' => ['nullable', 'integer', 'in:0,10,30,60,300'],
        ];
    }
}
