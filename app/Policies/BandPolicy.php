<?php

namespace App\Policies;

use App\Models\Band;
use App\Models\User;

class BandPolicy
{
  public function update(User $user, Band $band): bool
  {
    return $user->id === $band->created_by || $user->role === 'admin';
  }

  public function delete(User $user, Band $band): bool
  {
    return $this->update($user, $band);
  }
}
