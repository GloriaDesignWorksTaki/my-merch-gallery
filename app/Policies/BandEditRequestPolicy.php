<?php
/**
 * バンド編集申請データの操作権限の設定
 * @package App\Policies
 */
namespace App\Policies;

use App\Models\BandEditRequest;
use App\Models\User;

class BandEditRequestPolicy
{
  // 一覧はスタッフのみ
  public function viewAny(User $user): bool
  {
    return $user->isStaff();
  }
  // 保留中の申請だけ、スタッフが審査できる
  public function review(User $user, BandEditRequest $bandEditRequest): bool
  {
    return $user->isStaff() && $bandEditRequest->status === BandEditRequest::STATUS_PENDING;
  }
}
