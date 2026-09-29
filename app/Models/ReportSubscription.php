<?php

namespace App\Models;

use App\Models\Concerns\HasAuditFields;
use Database\Factories\ReportSubscriptionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

#[Fillable([
    'saved_report_id',
    'user_id',
    'frequency',
    'day_of_week',
    'day_of_month',
    'time_of_day',
    'next_run_at',
    'last_sent_at',
])]
class ReportSubscription extends Model
{
    /** @use HasFactory<ReportSubscriptionFactory> */
    use HasAuditFields, HasFactory;

    public const FREQUENCIES = ['daily', 'weekly', 'monthly'];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'day_of_month' => 'integer',
            'next_run_at' => 'datetime',
            'last_sent_at' => 'datetime',
        ];
    }

    public function savedReport(): BelongsTo
    {
        return $this->belongsTo(SavedReport::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function computeNextRunAt(?Carbon $from = null): Carbon
    {
        $from = ($from ?? now())->copy();
        $time = Carbon::parse($this->time_of_day)->format('H:i:s');

        return match ($this->frequency) {
            'daily' => $this->nextDaily($from, $time),
            'weekly' => $this->nextWeekly($from, $time),
            'monthly' => $this->nextMonthly($from, $time),
            default => $from->addDay()->setTimeFromTimeString($time),
        };
    }

    private function nextDaily(Carbon $from, string $time): Carbon
    {
        $candidate = $from->copy()->setTimeFromTimeString($time);
        if ($candidate->lte($from)) {
            $candidate->addDay();
        }

        return $candidate;
    }

    private function nextWeekly(Carbon $from, string $time): Carbon
    {
        $targetDow = (int) ($this->day_of_week ?? 1);
        $candidate = $from->copy()->setTimeFromTimeString($time);
        while ((int) $candidate->dayOfWeek !== $targetDow || $candidate->lte($from)) {
            $candidate->addDay();
        }

        return $candidate;
    }

    private function nextMonthly(Carbon $from, string $time): Carbon
    {
        $day = min(28, max(1, (int) ($this->day_of_month ?? 1)));
        $candidate = $from->copy()->day($day)->setTimeFromTimeString($time);
        if ($candidate->lte($from)) {
            $candidate->addMonthNoOverflow()->day($day)->setTimeFromTimeString($time);
        }

        return $candidate;
    }
}
