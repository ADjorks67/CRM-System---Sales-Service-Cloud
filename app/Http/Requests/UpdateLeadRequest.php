<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ClearsBlankStrings;
use App\Models\Lead;
use App\Support\PicklistOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLeadRequest extends FormRequest
{
    use ClearsBlankStrings;

    public function authorize(): bool
    {
        /** @var Lead $lead */
        $lead = $this->route('lead');

        return $this->user()?->can('update', $lead) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'salutation' => ['nullable', 'string', PicklistOptions::inRule('salutation')],
            'first_name' => ['nullable', 'string', 'max:40'],
            'last_name' => ['required', 'string', 'max:80'],
            'company' => ['required', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:128'],
            'email' => ['nullable', 'email', 'max:80'],
            'phone' => ['nullable', 'string', 'max:40'],
            'mobile' => ['nullable', 'string', 'max:40'],
            'status' => ['required', 'string', Rule::in(array_keys(PicklistOptions::editableLeadStatuses()))],
            'lead_source' => ['nullable', 'string', PicklistOptions::inRule('lead_source')],
            'rating' => ['nullable', 'string', PicklistOptions::inRule('rating')],
            'industry' => ['nullable', 'string', PicklistOptions::inRule('industry')],
            'annual_revenue' => ['nullable', 'numeric', 'min:0'],
            'number_of_employees' => ['nullable', 'integer', 'min:0'],
            'website' => ['nullable', 'string', 'max:255'],
            'street' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:80'],
            'state' => ['nullable', 'string', 'max:80'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string'],
            'save_action' => ['nullable', 'in:save,save_new'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->nullifyBlankStrings([
            'salutation', 'first_name', 'title', 'email', 'phone', 'mobile',
            'lead_source', 'rating', 'industry', 'website', 'description',
            'street', 'city', 'state', 'postal_code', 'country',
            'annual_revenue', 'number_of_employees',
        ]);
    }
}
