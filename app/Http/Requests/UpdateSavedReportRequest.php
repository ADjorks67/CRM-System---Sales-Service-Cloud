<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ClearsBlankStrings;
use App\Models\SavedReport;
use App\Support\ReportFieldCatalog;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSavedReportRequest extends FormRequest
{
    use ClearsBlankStrings;

    public function authorize(): bool
    {
        $savedReport = $this->route('savedReport');

        return $savedReport instanceof SavedReport && ($this->user()?->can('update', $savedReport) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var SavedReport $savedReport */
        $savedReport = $this->route('savedReport');

        return ReportFieldCatalog::validationRules($savedReport->report_type);
    }

    protected function prepareForValidation(): void
    {
        /** @var SavedReport $savedReport */
        $savedReport = $this->route('savedReport');

        $this->merge([
            'report_type' => $savedReport->report_type,
        ]);

        $this->nullifyBlankStrings(['description', 'folder']);
        $this->merge([
            'is_private' => $this->boolean('is_private', true),
            'folder' => $this->input('folder') ?: 'private',
        ]);
    }
}
