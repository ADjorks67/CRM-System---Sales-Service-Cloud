<?php

namespace App\Support;

use App\Enums\LeadStatus;
use App\Models\Picklist;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\In;

class PicklistOptions
{
    /**
     * @return array<string, string>
     */
    public static function options(string $category): array
    {
        return Picklist::query()
            ->category($category)
            ->pluck('label', 'value')
            ->all();
    }

    /**
     * @return list<string>
     */
    public static function values(string $category): array
    {
        return Picklist::query()
            ->category($category)
            ->pluck('value')
            ->all();
    }

    public static function inRule(string $category): In
    {
        return Rule::in(self::values($category));
    }

    /**
     * Lead statuses that may be set manually (excludes Converted — FR-LEAD-005).
     *
     * @return array<string, string>
     */
    public static function editableLeadStatuses(): array
    {
        $fromPicklist = Collection::make(self::options('lead_status'))
            ->reject(fn (string $label, string $value) => $value === 'converted')
            ->all();

        return $fromPicklist !== [] ? $fromPicklist : LeadStatus::editableOptions();
    }
}
