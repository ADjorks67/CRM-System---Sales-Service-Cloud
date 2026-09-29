<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ClearsBlankStrings;
use App\Models\CrmCase;
use App\Support\PicklistOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCrmCaseRequest extends FormRequest
{
    use ClearsBlankStrings;

    public function authorize(): bool
    {
        return $this->user()?->can('create', CrmCase::class) ?? false;
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
            'owner_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'save_action' => ['nullable', 'in:save,save_new'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->nullifyBlankStrings([
            'subject', 'type', 'reason', 'description', 'internal_comments',
            'contact_id', 'account_id', 'web_email', 'web_company', 'web_name', 'web_phone', 'owner_id',
        ]);

        if (! $this->filled('status')) {
            $this->merge(['status' => 'new']);
        }

        if (! $this->filled('origin')) {
            $this->merge(['origin' => 'web']);
        }

        if (! $this->filled('priority')) {
            $this->merge(['priority' => 'medium']);
        }
    }
}
