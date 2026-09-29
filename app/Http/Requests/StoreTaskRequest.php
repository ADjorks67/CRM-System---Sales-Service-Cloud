<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ClearsBlankStrings;
use App\Models\Task;
use App\Support\PicklistOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    use ClearsBlankStrings;

    public function authorize(): bool
    {
        return $this->user()?->can('create', Task::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', PicklistOptions::inRule('task_status')],
            'priority' => ['required', 'string', PicklistOptions::inRule('task_priority')],
            'due_date' => ['nullable', 'date'],
            'comments' => ['nullable', 'string'],
            'reminder_set' => ['sometimes', 'boolean'],
            'reminder_at' => ['nullable', 'date', 'required_if:reminder_set,1', 'required_if:reminder_set,true'],
            'related_type' => ['nullable', 'string', Rule::in(['account', 'contact', 'lead', 'opportunity', 'case'])],
            'related_id' => ['nullable', 'integer', 'required_with:related_type'],
            'contact_id' => ['nullable', 'integer', Rule::exists('contacts', 'id')],
            'owner_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'save_action' => ['nullable', 'in:save,save_new'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->nullifyBlankStrings([
            'due_date', 'comments', 'reminder_at', 'related_type', 'related_id', 'contact_id', 'owner_id',
        ]);

        $this->merge([
            'reminder_set' => $this->boolean('reminder_set'),
        ]);

        if (! $this->filled('status')) {
            $this->merge(['status' => 'not_started']);
        }

        if (! $this->filled('priority')) {
            $this->merge(['priority' => 'normal']);
        }
    }
}
