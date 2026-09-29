<?php

namespace App\Policies;

use App\Models\Contact;
use App\Models\User;

class ContactPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('contacts.view');
    }

    public function view(User $user, Contact $contact): bool
    {
        if (! $user->hasPermission('contacts.view')) {
            return false;
        }

        return Contact::query()->whereKey($contact->id)->visibleTo($user)->exists();
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('contacts.create');
    }

    public function update(User $user, Contact $contact): bool
    {
        if (! $user->hasPermission('contacts.update')) {
            return false;
        }

        return $contact->isWritableBy($user);
    }

    public function delete(User $user, Contact $contact): bool
    {
        if (! $user->hasPermission('contacts.delete')) {
            return false;
        }

        return $contact->isWritableBy($user);
    }

    public function changeOwner(User $user, Contact $contact): bool
    {
        return $this->update($user, $contact);
    }
}
