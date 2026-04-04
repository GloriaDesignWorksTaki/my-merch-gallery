<?php
/**
 * マーチデータの操作権限の設定
 * @package App\Policies
 */
namespace App\Policies;

use App\Models\MerchItem;
use App\Models\User;

class MerchItemPolicy
{
  // いいねはログイン済みなら可
  public function like(User $user, MerchItem $merchItem): bool
  {
    return true;
  }
  // 更新は作成者かスタッフ
  public function update(User $user, MerchItem $merchItem): bool
  {
    return $user->id === $merchItem->created_by || $user->isStaff();
  }
  // 削除は更新と同じ
  public function delete(User $user, MerchItem $merchItem): bool
  {
    return $this->update($user, $merchItem);
  }
}
