<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function ban(User $actor, User $target): bool
    {
        if (! $actor->isStaff() || $actor->id === $target->id) {
            return false;
        }
        if ($target->isOwner()) {
            return $actor->isOwner();
        }
        if ($target->isAdmin()) {
            return $actor->isOwner();
        }

        return true;
    }

    public function unban(User $actor, User $target): bool
    {
        return $this->ban($actor, $target);
    }

    public function updateRole(User $actor, User $target): bool
    {
        return $actor->isOwner() && $actor->id !== $target->id;
    }
}
