<?php

namespace App\Http\Controllers\MerchItem;

use App\Http\Controllers\Controller;
use App\Models\MerchItem;
use App\Models\MerchItemComment;
use App\Models\User;
use App\Notifications\MerchCommentLikedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MerchItemCommentLikeController extends Controller
{
    public function toggle(Request $request, MerchItem $merchItem, MerchItemComment $merchItemComment): RedirectResponse
    {
        abort_if($merchItemComment->merch_item_id !== $merchItem->id, 404);

        $userId = $request->user()->id;

        $created = false;

        DB::transaction(function () use ($merchItemComment, $userId, &$created) {
            $like = $merchItemComment->likes()->where('user_id', $userId)->first();

            if ($like !== null) {
                $like->delete();
            } else {
                $merchItemComment->likes()->create(['user_id' => $userId]);
                $created = true;
            }
        });

        if ($created && (int) $merchItemComment->user_id !== (int) $userId) {
            User::find($merchItemComment->user_id)?->notify(
                new MerchCommentLikedNotification($merchItem, $merchItemComment, $request->user())
            );
        }

        return back();
    }
}
