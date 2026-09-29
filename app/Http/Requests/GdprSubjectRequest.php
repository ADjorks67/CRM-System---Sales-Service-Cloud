<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GdprSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manageGdpr') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'subject_type' => ['required', 'string', Rule::in(['contact', 'lead', 'user'])],
            'subject_id' => ['required', 'integer', 'min:1'],
        ];
    }
}
