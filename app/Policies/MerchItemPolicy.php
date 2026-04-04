<?php

namespace App\Policies;

use App\Models\MerchItem;
use App\Models\User;

class MerchItemPolicy
{
    public function like(User $user, MerchItem $merchItem): bool
    {
        return true;
    }

    public function update(User $user, MerchItem $merchItem): bool
    {
        return $user->id === $merchItem->created_by || $user->isStaff();
    }

    public function delete(User $user, MerchItem $merchItem): bool
    {
        return $this->update($user, $merchItem);
    }
}
