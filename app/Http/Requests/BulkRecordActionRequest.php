<?php

namespace App\Http\Requests;

use App\Support\PicklistOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkRecordActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $actions = ['change_owner', 'delete'];

        if ($this->routeIs('leads.bulk') || $this->routeIs('cases.bulk')) {
            $actions[] = 'change_status';
        }

        if ($this->routeIs('opportunities.bulk')) {
            $actions[] = 'archive';
        }

        $statusRule = ['required_if:action,change_status', 'nullable', 'string'];

        if ($this->routeIs('cases.bulk')) {
            $statusRule[] = PicklistOptions::inRule('case_status');
        } else {
            $statusRule[] = Rule::in(array_keys(PicklistOptions::editableLeadStatuses()));
        }

        return [
            'action' => ['required', 'string', Rule::in($actions)],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
            'owner_id' => ['required_if:action,change_owner', 'nullable', 'integer', Rule::exists('users', 'id')],
            'status' => $statusRule,
            'notify_new_owner' => ['sometimes', 'boolean'],
            'transfer_open_activities' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
