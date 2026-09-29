<?php

namespace Database\Factories;

use App\Models\ReportSubscription;
use App\Models\SavedReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReportSubscription>
 */
class ReportSubscriptionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'saved_report_id' => SavedReport::factory(),
            'user_id' => User::factory(),
            'frequency' => 'daily',
            'day_of_week' => null,
            'day_of_month' => null,
            'time_of_day' => '07:00:00',
            'next_run_at' => now()->subMinute(),
            'last_sent_at' => null,
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn () => ['user_id' => $user->id]);
    }

    public function forReport(SavedReport $report): static
    {
        return $this->state(fn () => ['saved_report_id' => $report->id]);
    }
}
