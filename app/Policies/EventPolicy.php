<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('events.view');
    }

    public function view(User $user, Event $event): bool
    {
        if (! $user->hasPermission('events.view')) {
            return false;
        }

        return Event::query()->whereKey($event->id)->visibleTo($user)->exists();
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('events.create');
    }

    public function update(User $user, Event $event): bool
    {
        if (! $user->hasPermission('events.update')) {
            return false;
        }

        return $event->isWritableBy($user);
    }

    public function delete(User $user, Event $event): bool
    {
        if (! $user->hasPermission('events.delete')) {
            return false;
        }

        return $event->isWritableBy($user);
    }

    public function reschedule(User $user, Event $event): bool
    {
        return $this->update($user, $event);
    }
}
