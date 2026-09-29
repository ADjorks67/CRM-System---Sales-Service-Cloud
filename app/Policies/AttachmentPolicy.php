<?php

namespace App\Policies;

use App\Models\Attachment;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AttachmentPolicy
{
    public function view(User $user, Attachment $attachment): bool
    {
        $parent = $attachment->attachable;

        return $parent instanceof Model && $user->can('view', $parent);
    }

    public function create(User $user, ?Model $attachable = null): bool
    {
        if ($attachable === null) {
            return $user->id !== null;
        }

        return $user->can('update', $attachable);
    }

    public function delete(User $user, Attachment $attachment): bool
    {
        $parent = $attachment->attachable;

        return $parent instanceof Model && $user->can('update', $parent);
    }

    public function download(User $user, Attachment $attachment): bool
    {
        return $this->view($user, $attachment) && $attachment->scan_status === Attachment::SCAN_CLEAN;
    }
}
