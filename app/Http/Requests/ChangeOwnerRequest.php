<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeOwnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'owner_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'notify_new_owner' => ['sometimes', 'boolean'],
            'transfer_open_activities' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
