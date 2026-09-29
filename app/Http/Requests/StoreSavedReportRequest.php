<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ClearsBlankStrings;
use App\Models\SavedReport;
use App\Support\ReportFieldCatalog;
use Illuminate\Foundation\Http\FormRequest;

class StoreSavedReportRequest extends FormRequest
{
    use ClearsBlankStrings;

    public function authorize(): bool
    {
        return $this->user()?->can('create', SavedReport::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $type = (string) $this->input('report_type', 'lead');

        return ReportFieldCatalog::validationRules($type);
    }

    protected function prepareForValidation(): void
    {
        $this->nullifyBlankStrings(['description', 'folder']);
        $this->merge([
            'is_private' => $this->boolean('is_private', true),
            'folder' => $this->input('folder') ?: 'private',
        ]);
    }
}
