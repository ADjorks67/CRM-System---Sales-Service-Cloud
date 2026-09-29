<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('tasks.view');
    }

    public function view(User $user, Task $task): bool
    {
        if (! $user->hasPermission('tasks.view')) {
            return false;
        }

        return Task::query()->whereKey($task->id)->visibleTo($user)->exists();
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('tasks.create');
    }

    public function update(User $user, Task $task): bool
    {
        if (! $user->hasPermission('tasks.update')) {
            return false;
        }

        return $task->isWritableBy($user);
    }

    public function delete(User $user, Task $task): bool
    {
        if (! $user->hasPermission('tasks.delete')) {
            return false;
        }

        return $task->isWritableBy($user);
    }

    public function changeOwner(User $user, Task $task): bool
    {
        return $this->update($user, $task);
    }

    public function complete(User $user, Task $task): bool
    {
        return $this->update($user, $task);
    }
}
