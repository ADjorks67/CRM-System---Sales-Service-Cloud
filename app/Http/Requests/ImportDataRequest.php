<?php

namespace App\Http\Requests;

use App\Services\DataImportService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ImportDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        $object = (string) ($this->input('object') ?: session('import.object'));
        $service = app(DataImportService::class);

        return $this->user() !== null
            && isset($service->objects()[$object])
            && $service->authorizeImport($this->user(), $object);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $service = app(DataImportService::class);
        $objects = array_keys($service->objects());
        $object = (string) ($this->input('object') ?: session('import.object'));
        $fields = $service->objects()[$object]['fields'] ?? [];

        $rules = [
            'object' => ['required', 'string', Rule::in($objects)],
            'update_or_insert' => ['sometimes', 'boolean'],
            'mapping' => ['required', 'array'],
        ];

        foreach (array_keys($fields) as $field) {
            $rules["mapping.{$field}"] = ['nullable', 'integer', 'min:0'];
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('object') && session()->has('import.object')) {
            $this->merge(['object' => session('import.object')]);
        }

        $mapping = collect($this->input('mapping', []))
            ->map(fn ($value) => $value === '' || $value === null ? null : (int) $value)
            ->all();

        $this->merge([
            'mapping' => $mapping,
            'update_or_insert' => $this->boolean('update_or_insert'),
        ]);
    }
}
