<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMeetingLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->hasRole('Admin', 'sanctum');
    }

    public function rules(): array
    {
        return [
            'meeting_url' => 'required|url|max:2048',
            'resend_email' => 'sometimes|boolean',
        ];
    }
}
