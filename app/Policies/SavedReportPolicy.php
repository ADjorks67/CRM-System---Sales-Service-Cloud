<?php

namespace App\Policies;

use App\Models\SavedReport;
use App\Models\User;

class SavedReportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('reports.view');
    }

    public function view(User $user, SavedReport $savedReport): bool
    {
        if (! $user->hasPermission('reports.view')) {
            return false;
        }

        return SavedReport::query()->whereKey($savedReport->id)->visibleTo($user)->exists();
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('reports.create');
    }

    public function update(User $user, SavedReport $savedReport): bool
    {
        if (! $user->hasPermission('reports.update')) {
            return false;
        }

        return $savedReport->isWritableBy($user);
    }

    public function delete(User $user, SavedReport $savedReport): bool
    {
        if (! $user->hasPermission('reports.delete')) {
            return false;
        }

        return $savedReport->isWritableBy($user);
    }

    public function export(User $user, SavedReport $savedReport): bool
    {
        return $this->view($user, $savedReport);
    }
}
