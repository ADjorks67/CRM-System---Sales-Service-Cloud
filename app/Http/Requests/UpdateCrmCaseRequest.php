<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ClearsBlankStrings;
use App\Models\CrmCase;
use App\Support\PicklistOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCrmCaseRequest extends FormRequest
{
    use ClearsBlankStrings;

    public function authorize(): bool
    {
        /** @var CrmCase $crmCase */
        $crmCase = $this->route('crmCase');

        return $this->user()?->can('update', $crmCase) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'subject' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', PicklistOptions::inRule('case_status')],
            'priority' => ['nullable', 'string', PicklistOptions::inRule('case_priority')],
            'origin' => ['required', 'string', PicklistOptions::inRule('case_origin')],
            'type' => ['nullable', 'string', PicklistOptions::inRule('case_type')],
            'reason' => ['nullable', 'string', PicklistOptions::inRule('case_reason')],
            'description' => ['nullable', 'string'],
            'internal_comments' => ['nullable', 'string'],
            'contact_id' => ['nullable', 'integer', Rule::exists('contacts', 'id')],
            'account_id' => ['nullable', 'integer', Rule::exists('accounts', 'id')],
            'web_email' => ['nullable', 'email', 'max:80'],
            'web_company' => ['nullable', 'string', 'max:255'],
            'web_name' => ['nullable', 'string', 'max:255'],
            'web_phone' => ['nullable', 'string', 'max:40'],
            'save_action' => ['nullable', 'in:save,save_new'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->nullifyBlankStrings([
            'subject', 'type', 'reason', 'description', 'internal_comments',
            'contact_id', 'account_id', 'web_email', 'web_company', 'web_name', 'web_phone',
        ]);
    }
}
