<?php

namespace App\Http\Requests;

use App\Services\DataImportService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PreviewImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        $object = (string) $this->input('object');
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
        $objects = array_keys(app(DataImportService::class)->objects());

        return [
            'object' => ['required', 'string', Rule::in($objects)],
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
            'update_or_insert' => ['sometimes', 'boolean'],
        ];
    }
}
