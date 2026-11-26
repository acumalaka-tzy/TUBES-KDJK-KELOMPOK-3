<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendSignalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'target_user_id' => ['required', 'string', 'max:64'],
            'type' => ['required', 'string', 'in:offer,answer,candidate,ice-restart,renegotiate,bye'],
            'signal' => ['required', 'array'],
        ];
    }
}
