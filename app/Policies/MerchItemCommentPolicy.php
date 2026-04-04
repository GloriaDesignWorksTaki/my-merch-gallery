<?php

namespace App\Policies;

use App\Models\MerchItemComment;
use App\Models\User;

class MerchItemCommentPolicy
{
    public function delete(User $user, MerchItemComment $comment): bool
    {
        return $user->id === $comment->user_id || $user->isStaff();
    }
}
