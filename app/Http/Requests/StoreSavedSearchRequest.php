<?php

namespace App\Http\Requests;

use App\Models\SavedSearch;
use App\Services\AdvancedSearchService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSavedSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', SavedSearch::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'object_type' => ['required', 'string', Rule::in(array_keys(AdvancedSearchService::OBJECTS))],
            'conditions' => ['nullable', 'array', 'max:20'],
            'conditions.*.field' => ['required_with:conditions', 'string', 'max:64'],
            'conditions.*.operator' => ['required_with:conditions', 'string', Rule::in(AdvancedSearchService::OPERATORS)],
            'conditions.*.value' => ['nullable', 'string', 'max:255'],
            'conditions.*.logic' => ['nullable', 'string', Rule::in(['AND', 'OR', 'and', 'or'])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'date_field' => ['nullable', 'string', 'max:64'],
        ];
    }
}
