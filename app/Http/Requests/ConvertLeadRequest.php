<?php

namespace App\Http\Requests;

use App\Models\Lead;
use App\Support\PicklistOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConvertLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        $lead = $this->route('lead');

        return $lead instanceof Lead && ($this->user()?->can('convert', $lead) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'account_action' => ['required', 'in:create,match'],
            'account_id' => [
                Rule::requiredIf(fn () => $this->input('account_action') === 'match'),
                'nullable',
                'integer',
                Rule::exists('accounts', 'id'),
            ],
            'account_name' => [
                Rule::requiredIf(fn () => $this->input('account_action') === 'create'),
                'nullable',
                'string',
                'max:255',
            ],
            'create_opportunity' => ['sometimes', 'boolean'],
            'opportunity_name' => [
                Rule::requiredIf(fn () => $this->boolean('create_opportunity')),
                'nullable',
                'string',
                'max:120',
            ],
            'opportunity_amount' => ['nullable', 'numeric', 'min:0'],
            'opportunity_close_date' => [
                Rule::requiredIf(fn () => $this->boolean('create_opportunity')),
                'nullable',
                'date',
                'after_or_equal:today',
            ],
            'opportunity_stage' => [
                Rule::requiredIf(fn () => $this->boolean('create_opportunity')),
                'nullable',
                'string',
                PicklistOptions::inRule('opportunity_stage'),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'create_opportunity' => $this->boolean('create_opportunity'),
        ]);
    }
}
