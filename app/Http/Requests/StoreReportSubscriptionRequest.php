<?php

namespace App\Http\Requests;

use App\Models\ReportSubscription;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReportSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $report = $this->route('saved_report');

        return $this->user()?->can('create', [ReportSubscription::class, $report]) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'frequency' => ['required', 'string', Rule::in(ReportSubscription::FREQUENCIES)],
            'day_of_week' => ['nullable', 'integer', 'between:0,6', 'required_if:frequency,weekly'],
            'day_of_month' => ['nullable', 'integer', 'between:1,28', 'required_if:frequency,monthly'],
            'time_of_day' => ['required', 'date_format:H:i'],
        ];
    }
}
