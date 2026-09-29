<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ClearsBlankStrings;
use App\Models\Dashboard;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDashboardRequest extends FormRequest
{
    use ClearsBlankStrings;

    public function authorize(): bool
    {
        return $this->user()?->can('create', Dashboard::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'folder' => ['nullable', 'string', 'max:64'],
            'is_private' => ['sometimes', 'boolean'],
            'refresh_interval_minutes' => ['nullable', 'integer', Rule::in(Dashboard::REFRESH_INTERVALS)],
            'widgets' => ['nullable', 'array', 'max:20'],
            'widgets.*.title' => ['required', 'string', 'max:120'],
            'widgets.*.widget_type' => ['required', 'string', Rule::in(['chart', 'table', 'metric', 'gauge'])],
            'widgets.*.source_type' => ['required', 'string', Rule::in(['prebuilt', 'saved_report'])],
            'widgets.*.source_key' => ['nullable', 'string', 'max:120'],
            'widgets.*.saved_report_id' => ['nullable', 'integer', Rule::exists('saved_reports', 'id')],
            'widgets.*.grid_x' => ['nullable', 'integer', 'min:0', 'max:11'],
            'widgets.*.grid_y' => ['nullable', 'integer', 'min:0', 'max:50'],
            'widgets.*.grid_w' => ['nullable', 'integer', 'min:1', 'max:12'],
            'widgets.*.grid_h' => ['nullable', 'integer', 'min:1', 'max:12'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->nullifyBlankStrings(['description', 'folder', 'refresh_interval_minutes']);
        $this->merge([
            'is_private' => $this->boolean('is_private', true),
            'folder' => $this->input('folder') ?: 'private',
        ]);
    }
}
