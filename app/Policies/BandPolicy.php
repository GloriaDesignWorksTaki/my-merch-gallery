<?php
/**
 * バンドデータの操作権限の設定
 * @package App\Policies
 */
namespace App\Policies;

use App\Models\Band;
use App\Models\User;

class BandPolicy
{
  // いいねはログイン済みなら可
  public function like(User $user, Band $band): bool
  {
    return true;
  }
  // 更新・削除はスタッフのみ
  public function update(User $user, Band $band): bool
  {
    return $user->isStaff();
  }
  // 編集依頼は一般のみ（BAN・スタッフは不可）
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
  // 削除は更新と同じ
  public function delete(User $user, Band $band): bool
  {
    return $this->update($user, $band);
  }
}
