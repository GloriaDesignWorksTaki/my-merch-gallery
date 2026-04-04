<?php

namespace App\Policies;

use App\Models\Band;
use App\Models\User;

class BandPolicy
{
    public function like(User $user, Band $band): bool
    {
        return true;
    }

    public function update(User $user, Band $band): bool
    {
        return $user->isStaff();
    }

    public function createEditRequest(User $user, Band $band): bool
    {
        if ($user->isBanned()) {
            return false;
        }

        if ($user->isStaff()) {
            return false;
        }

        return true;
    }

    public function delete(User $user, Band $band): bool
    {
        return $this->update($user, $band);
    }
}
