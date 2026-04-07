<?php
/**
 * マーチいいねの付け外し（通知）
 * @package App\Http\Controllers\MerchItem
 */
namespace App\Http\Controllers\MerchItem;

use App\Http\Controllers\Controller;
use App\Models\MerchItem;
use App\Models\User;
use App\Notifications\MerchItemLikedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MerchItemLikeController extends Controller
{
  public function toggle(Request $request, MerchItem $merchItem): RedirectResponse
  {
    $this->authorize('like', $merchItem);

    $userId = $request->user()->id;

    DB::transaction(function () use ($merchItem, $userId, $request) {
      $like = $merchItem->likes()->where('user_id', $userId)->first();

      if ($like !== null) {
        $like->delete();
      } else {
        $merchItem->likes()->create(['user_id' => $userId]);
        $ownerId = $merchItem->created_by;
        if ($ownerId !== null && (int) $ownerId !== (int) $userId) {
          User::find($ownerId)?->notify(
            new MerchItemLikedNotification($merchItem, $request->user())
          );
        }
      }
    });

    return back();
  }
}
