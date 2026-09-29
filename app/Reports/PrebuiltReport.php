<?php

namespace App\Reports;

use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

abstract class PrebuiltReport
{
    abstract public function key(): string;

    abstract public function title(): string;

    abstract public function category(): string;

    /**
     * @return list<string>
     */
    abstract public function columns(): array;

    /**
     * @return Collection<int, array<string, mixed>>
     */
    abstract public function rows(User $user, CarbonInterface $from, CarbonInterface $to): Collection;

    /**
     * Optional chart payload for FR-RPT-004.
     *
     * @return array{type: string, labels: list<string>, values: list<float|int>, label?: string}|null
     */
    public function chart(User $user, CarbonInterface $from, CarbonInterface $to): ?array
    {
        return null;
    }

    public function description(): string
    {
        return '';
    }
}
