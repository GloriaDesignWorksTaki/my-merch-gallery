<?php
/**
 * ユーザー管理の操作権限の設定（BAN・ロールなど）
 * @package App\Policies
 */
namespace App\Policies;

use App\Models\User;

class UserPolicy
{
  // 一覧はスタッフのみ
  public function viewAny(User $user): bool
  {
    return $user->isStaff();
  }
  // BAN（自分は不可。相手がオーナー／管理者なら実行者も制限）
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

  // BAN 解除は BAN と同じ条件
  public function unban(User $actor, User $target): bool
  {
    return $this->ban($actor, $target);
  }
  // ロール変更はオーナーのみ（自分は不可）
  public function updateRole(User $actor, User $target): bool
  {
    return $actor->isOwner() && $actor->id !== $target->id;
  }
}
