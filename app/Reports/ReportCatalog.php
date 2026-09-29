<?php

namespace App\Reports;

use App\Reports\Cases\AverageCaseAgeReport;
use App\Reports\Cases\MonthlyCaseVolumeByChannelReport;
use App\Reports\Leads\ConversionOfNewLeadsThisFyReport;
use App\Reports\Leads\LeadsBySourceThisFyReport;
use App\Reports\Leads\LeadsConvertedThisFyReport;
use App\Reports\Leads\LeadsCreatedByMonthReport;
use App\Reports\Leads\NewLeadsThisFyByOwnerReport;
use App\Reports\Opportunities\AllPipelineCurrentYearReport;
use App\Reports\Opportunities\AvgDealLengthReport;
use App\Reports\Opportunities\AvgDealSizeCurrentFyReport;
use App\Reports\Opportunities\ClosedWonByOwnerReport;
use App\Reports\Opportunities\ClosedWonThisFyReport;
use App\Reports\Opportunities\PotentialRevenueSourceCurrentYearReport;
use InvalidArgumentException;

class ReportCatalog
{
    /**
     * @return list<class-string<PrebuiltReport>>
     */
    public static function classes(): array
    {
        return [
            LeadsBySourceThisFyReport::class,
            LeadsConvertedThisFyReport::class,
            LeadsCreatedByMonthReport::class,
            NewLeadsThisFyByOwnerReport::class,
            ConversionOfNewLeadsThisFyReport::class,
            AllPipelineCurrentYearReport::class,
            PotentialRevenueSourceCurrentYearReport::class,
            AvgDealSizeCurrentFyReport::class,
            AvgDealLengthReport::class,
            ClosedWonThisFyReport::class,
            ClosedWonByOwnerReport::class,
            AverageCaseAgeReport::class,
            MonthlyCaseVolumeByChannelReport::class,
        ];
    }

    /**
     * @return list<PrebuiltReport>
     */
    public static function all(): array
    {
        return array_map(fn (string $class) => new $class, self::classes());
    }

    public static function find(string $key): PrebuiltReport
    {
        foreach (self::all() as $report) {
            if ($report->key() === $key) {
                return $report;
            }
        }

        throw new InvalidArgumentException("Unknown report [{$key}].");
    }
}
