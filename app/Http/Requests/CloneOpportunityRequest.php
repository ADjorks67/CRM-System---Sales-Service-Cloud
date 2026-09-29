<?php

namespace App\Http\Requests;

use App\Models\Opportunity;
use Illuminate\Foundation\Http\FormRequest;

class CloneOpportunityRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Opportunity $opportunity */
        $opportunity = $this->route('opportunity');

        return $this->user()?->can('clone', $opportunity) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'include_related' => ['sometimes', 'boolean'],
        ];
    }
}
