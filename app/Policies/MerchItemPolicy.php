<?php

namespace App\Policies;

use App\Models\MerchItem;
use App\Models\User;

class MerchItemPolicy
{
  public function update(User $user, MerchItem $merchItem): bool
  {
    return $user->id === $merchItem->created_by || $user->role === 'admin';
  }

  public function delete(User $user, MerchItem $merchItem): bool
  {
    return $this->update($user, $merchItem);
  }
}
