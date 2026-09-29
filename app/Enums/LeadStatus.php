<?php

namespace App\Enums;

enum LeadStatus: string
{
    case New = 'new';
    case Working = 'working';
    case Nurturing = 'nurturing';
    case Qualified = 'qualified';
    case Unqualified = 'unqualified';
    case Converted = 'converted';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Working => 'Working',
            self::Nurturing => 'Nurturing',
            self::Qualified => 'Qualified',
            self::Unqualified => 'Unqualified',
            self::Converted => 'Converted',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }

    /**
     * Statuses settable outside the Phase 4 conversion wizard (FR-LEAD-005/006).
     *
     * @return array<string, string>
     */
    public static function editableOptions(): array
    {
        return collect(self::options())
            ->reject(fn (string $label, string $value) => $value === self::Converted->value)
            ->all();
    }
}
