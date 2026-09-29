<?php

namespace App\Policies;

use App\Models\ReportSubscription;
use App\Models\SavedReport;
use App\Models\User;

class ReportSubscriptionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('reports.view');
    }

    public function view(User $user, ReportSubscription $reportSubscription): bool
    {
        return (int) $reportSubscription->user_id === (int) $user->id
            && $user->can('view', $reportSubscription->savedReport);
    }

    public function create(User $user, ?SavedReport $savedReport = null): bool
    {
        if (! $user->hasPermission('reports.view')) {
            return false;
        }

        if ($savedReport === null) {
            return true;
        }

        return $user->can('view', $savedReport);
    }

    public function update(User $user, ReportSubscription $reportSubscription): bool
    {
        return (int) $reportSubscription->user_id === (int) $user->id
            && $user->can('view', $reportSubscription->savedReport);
    }

    public function delete(User $user, ReportSubscription $reportSubscription): bool
    {
        return (int) $reportSubscription->user_id === (int) $user->id;
    }
}
