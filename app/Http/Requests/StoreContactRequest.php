<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ClearsBlankStrings;
use App\Models\Contact;
use App\Support\PicklistOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContactRequest extends FormRequest
{
    use ClearsBlankStrings;

    public function authorize(): bool
    {
        return $this->user()?->can('create', Contact::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'account_id' => ['required', 'integer', Rule::exists('accounts', 'id')],
            'salutation' => ['nullable', 'string', PicklistOptions::inRule('salutation')],
            'first_name' => ['nullable', 'string', 'max:40'],
            'middle_name' => ['nullable', 'string', 'max:40'],
            'last_name' => ['required', 'string', 'max:80'],
            'title' => ['nullable', 'string', 'max:128'],
            'department' => ['nullable', 'string', 'max:80'],
            'phone' => ['nullable', 'string', 'max:40'],
            'mobile' => ['nullable', 'string', 'max:40'],
            'home_phone' => ['nullable', 'string', 'max:40'],
            'other_phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:80'],
            'fax' => ['nullable', 'string', 'max:40'],
            'reports_to_id' => ['nullable', 'integer', Rule::exists('contacts', 'id')],
            'assistant' => ['nullable', 'string', 'max:80'],
            'asst_phone' => ['nullable', 'string', 'max:40'],
            'mailing_street' => ['nullable', 'string', 'max:255'],
            'mailing_city' => ['nullable', 'string', 'max:80'],
            'mailing_state' => ['nullable', 'string', 'max:80'],
            'mailing_postal_code' => ['nullable', 'string', 'max:20'],
            'mailing_country' => ['nullable', 'string', 'max:80'],
            'other_street' => ['nullable', 'string', 'max:255'],
            'other_city' => ['nullable', 'string', 'max:80'],
            'other_state' => ['nullable', 'string', 'max:80'],
            'other_postal_code' => ['nullable', 'string', 'max:20'],
            'other_country' => ['nullable', 'string', 'max:80'],
            'lead_source' => ['nullable', 'string', PicklistOptions::inRule('lead_source')],
            'birthdate' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
            'owner_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'save_action' => ['nullable', 'in:save,save_new'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->nullifyBlankStrings([
            'salutation', 'first_name', 'middle_name', 'title', 'department',
            'phone', 'mobile', 'home_phone', 'other_phone', 'email', 'fax',
            'reports_to_id', 'assistant', 'asst_phone', 'lead_source', 'birthdate',
            'description', 'owner_id',
            'mailing_street', 'mailing_city', 'mailing_state', 'mailing_postal_code', 'mailing_country',
            'other_street', 'other_city', 'other_state', 'other_postal_code', 'other_country',
        ]);
    }
}
