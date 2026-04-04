<?php

namespace App\Policies;

use App\Models\BandEditRequest;
use App\Models\User;

class BandEditRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function review(User $user, BandEditRequest $bandEditRequest): bool
    {
        return $user->isStaff() && $bandEditRequest->status === BandEditRequest::STATUS_PENDING;
    }
}
