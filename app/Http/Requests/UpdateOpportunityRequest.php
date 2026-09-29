<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ClearsBlankStrings;
use App\Models\Opportunity;
use App\Support\PicklistOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOpportunityRequest extends FormRequest
{
    use ClearsBlankStrings;

    public function authorize(): bool
    {
        /** @var Opportunity $opportunity */
        $opportunity = $this->route('opportunity');

        return $this->user()?->can('update', $opportunity) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Opportunity $opportunity */
        $opportunity = $this->route('opportunity');

        $closeDateRules = ['required', 'date'];
        if (! $opportunity->is_closed) {
            $closeDateRules[] = 'after_or_equal:today';
        }

        return [
            'name' => ['required', 'string', 'max:120'],
            'account_id' => ['required', 'integer', Rule::exists('accounts', 'id')],
            'close_date' => $closeDateRules,
            'amount' => ['nullable', 'numeric', 'min:0'],
            'type' => ['nullable', 'string', PicklistOptions::inRule('opportunity_type')],
            'lead_source' => ['nullable', 'string', PicklistOptions::inRule('lead_source')],
            'next_step' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'probability' => ['nullable', 'integer', 'min:0', 'max:100'],
            'save_action' => ['nullable', 'in:save,save_new'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->nullifyBlankStrings([
            'type', 'lead_source', 'next_step', 'description', 'amount', 'probability',
        ]);
    }
}
