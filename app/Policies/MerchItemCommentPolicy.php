<?php
/**
 * マーチコメントデータの操作権限の設定
 * @package App\Policies
 */
namespace App\Policies;

use App\Models\MerchItemComment;
use App\Models\User;

class MerchItemCommentPolicy
{
  // 自分のコメントかスタッフが削除できる
  public function delete(User $user, MerchItemComment $comment): bool
  {
    return $user->id === $comment->user_id || $user->isStaff();
  }
}
