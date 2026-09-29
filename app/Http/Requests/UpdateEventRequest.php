<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ClearsBlankStrings;
use App\Models\Event;
use App\Support\PicklistOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateEventRequest extends FormRequest
{
    use ClearsBlankStrings;

    public function authorize(): bool
    {
        $event = $this->route('event');

        return $event instanceof Event
            && ($this->user()?->can('update', $event) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after_or_equal:starts_at'],
            'is_all_day' => ['sometimes', 'boolean'],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'show_as' => ['nullable', 'string', PicklistOptions::inRule('event_show_as')],
            'is_private' => ['sometimes', 'boolean'],
            'calendar_type' => ['nullable', 'string', Rule::in(['my_events'])],
            'color' => ['nullable', 'string', 'max:32'],
            'owner_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'related_type' => ['nullable', 'string', Rule::in(['account', 'contact', 'lead', 'opportunity'])],
            'related_id' => ['nullable', 'integer', 'required_with:related_type'],
            'name_contact_id' => ['nullable', 'integer', Rule::exists('contacts', 'id')],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $type = $this->input('related_type');
            $id = $this->input('related_id');

            if ($type && $id) {
                $table = match ($type) {
                    'account' => 'accounts',
                    'contact' => 'contacts',
                    'lead' => 'leads',
                    'opportunity' => 'opportunities',
                    default => null,
                };

                if ($table === null || ! DB::table($table)->where('id', $id)->exists()) {
                    $validator->errors()->add('related_id', 'The selected related record is invalid.');
                }
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $this->nullifyBlankStrings([
            'location', 'description', 'show_as', 'color', 'owner_id',
            'related_type', 'related_id', 'name_contact_id', 'calendar_type',
        ]);

        $this->merge([
            'is_all_day' => $this->boolean('is_all_day'),
            'is_private' => $this->boolean('is_private'),
            'show_as' => $this->input('show_as') ?: 'busy',
            'calendar_type' => $this->input('calendar_type') ?: 'my_events',
        ]);
    }
}
