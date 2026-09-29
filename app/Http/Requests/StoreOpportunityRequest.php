<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ClearsBlankStrings;
use App\Models\Opportunity;
use App\Support\PicklistOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOpportunityRequest extends FormRequest
{
    use ClearsBlankStrings;

    public function authorize(): bool
    {
        return $this->user()?->can('create', Opportunity::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'account_id' => ['required', 'integer', Rule::exists('accounts', 'id')],
            'close_date' => ['required', 'date', 'after_or_equal:today'],
            'stage' => ['required', 'string', PicklistOptions::inRule('opportunity_stage')],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'type' => ['nullable', 'string', PicklistOptions::inRule('opportunity_type')],
            'lead_source' => ['nullable', 'string', PicklistOptions::inRule('lead_source')],
            'next_step' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'owner_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'probability' => ['nullable', 'integer', 'min:0', 'max:100'],
            'save_action' => ['nullable', 'in:save,save_new'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->nullifyBlankStrings([
            'type', 'lead_source', 'next_step', 'description', 'owner_id', 'amount', 'probability',
        ]);

        if (! $this->filled('stage')) {
            $this->merge(['stage' => 'qualification']);
        }
    }
}
