<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ClearsBlankStrings;
use App\Models\Account;
use App\Services\AccountHierarchyService;
use App\Support\PicklistOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateAccountRequest extends FormRequest
{
    use ClearsBlankStrings;

    public function authorize(): bool
    {
        /** @var Account $account */
        $account = $this->route('account');

        return $this->user()?->can('update', $account) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Account $account */
        $account = $this->route('account');

        return [
            'name' => ['required', 'string', 'max:255'],
            'parent_account_id' => [
                'nullable',
                'integer',
                Rule::exists('accounts', 'id'),
                Rule::notIn([(string) $account->id]),
            ],
            'phone' => ['nullable', 'string', 'max:40'],
            'fax' => ['nullable', 'string', 'max:40'],
            'website' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', PicklistOptions::inRule('account_type')],
            'industry' => ['nullable', 'string', PicklistOptions::inRule('industry')],
            'employees' => ['nullable', 'integer', 'min:0'],
            'annual_revenue' => ['nullable', 'numeric', 'min:0'],
            'billing_street' => ['nullable', 'string', 'max:255'],
            'billing_city' => ['nullable', 'string', 'max:80'],
            'billing_state' => ['nullable', 'string', 'max:80'],
            'billing_postal_code' => ['nullable', 'string', 'max:20'],
            'billing_country' => ['nullable', 'string', 'max:80'],
            'shipping_street' => ['nullable', 'string', 'max:255'],
            'shipping_city' => ['nullable', 'string', 'max:80'],
            'shipping_state' => ['nullable', 'string', 'max:80'],
            'shipping_postal_code' => ['nullable', 'string', 'max:20'],
            'shipping_country' => ['nullable', 'string', 'max:80'],
            'copy_billing_to_shipping' => ['sometimes', 'boolean'],
            'description' => ['nullable', 'string'],
            'save_action' => ['nullable', 'in:save,save_new'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            /** @var Account $account */
            $account = $this->route('account');
            $parentId = $this->input('parent_account_id');

            if ($parentId === null || $parentId === '') {
                return;
            }

            if (app(AccountHierarchyService::class)->wouldCreateCycle($account, (int) $parentId)) {
                $validator->errors()->add(
                    'parent_account_id',
                    'Parent account cannot be this account or one of its descendants.'
                );
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $this->nullifyBlankStrings([
            'parent_account_id', 'type', 'industry',
            'phone', 'fax', 'website', 'description',
            'billing_street', 'billing_city', 'billing_state', 'billing_postal_code', 'billing_country',
            'shipping_street', 'shipping_city', 'shipping_state', 'shipping_postal_code', 'shipping_country',
            'employees', 'annual_revenue',
        ]);

        if ($this->boolean('copy_billing_to_shipping')) {
            $this->merge([
                'shipping_street' => $this->input('billing_street'),
                'shipping_city' => $this->input('billing_city'),
                'shipping_state' => $this->input('billing_state'),
                'shipping_postal_code' => $this->input('billing_postal_code'),
                'shipping_country' => $this->input('billing_country'),
            ]);
        }
    }
}
