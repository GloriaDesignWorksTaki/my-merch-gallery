<?php

namespace App\Http\Controllers\MerchItem;

use App\Http\Controllers\Controller;
use App\Http\Requests\MerchItem\StoreMerchItemCommentRequest;
use App\Models\MerchItem;
use App\Models\MerchItemComment;
use App\Models\User;
use App\Notifications\MerchCommentReplyNotification;
use App\Notifications\MerchItemCommentedNotification;
use Illuminate\Http\RedirectResponse;

class MerchItemCommentController extends Controller
{
    public function store(StoreMerchItemCommentRequest $request, MerchItem $merchItem): RedirectResponse
    {
        $validated = $request->validated();
        $parentId = $validated['parent_id'] ?? null;
        $parent = null;

        if ($parentId !== null) {
            $parent = MerchItemComment::query()
                ->whereKey($parentId)
                ->where('merch_item_id', $merchItem->id)
                ->firstOrFail();

            if ($parent->parent_id !== null) {
                return back()
                    ->withErrors(['parent_id' => __('validation.comment_reply_depth')])
                    ->withInput();
            }
        }

        $comment = $merchItem->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
            'parent_id' => $parentId,
        ]);

        $actor = $request->user();

        if ($parentId === null) {
            if ($merchItem->created_by !== null && (int) $merchItem->created_by !== (int) $actor->id) {
                User::find($merchItem->created_by)?->notify(
                    new MerchItemCommentedNotification($merchItem, $comment, $actor)
                );
            }
        } elseif ($parent !== null && (int) $parent->user_id !== (int) $actor->id) {
            $parent->user->notify(
                new MerchCommentReplyNotification($merchItem, $parent, $comment, $actor)
            );
        }

        return back()->with('status', 'merch-comment-created');
    }

    public function destroy(MerchItem $merchItem, MerchItemComment $merchItemComment): RedirectResponse
    {
        abort_if($merchItemComment->merch_item_id !== $merchItem->id, 404);

        $this->authorize('delete', $merchItemComment);

        $merchItemComment->delete();

        return back()->with('status', 'merch-comment-deleted');
    }
}
