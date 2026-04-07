<?php
/**
 * マーチコメントへ返信が付いたときの通知の設定
 * @package App\Notifications
 */
namespace App\Notifications;

use App\Models\MerchItem;
use App\Models\MerchItemComment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class MerchCommentReplyNotification extends Notification
{
  use Queueable;

  public function __construct(
    public MerchItem $merchItem,
    public MerchItemComment $parentComment,
    public MerchItemComment $reply,
    public User $actor,
  ) {}
  // DB にだけ送る
  public function via(object $notifiable): array
  {
    return ['database'];
  }
  // 一覧・遷移用のペイロード（返信本文は先頭だけ）
  public function toDatabase(object $notifiable): array
  {
    return [
      'type' => 'merch_comment_replied',
      'merch_item_id' => $this->merchItem->id,
      'merch_item_slug' => $this->merchItem->slug,
      'merch_item_name' => $this->merchItem->name,
      'parent_comment_id' => $this->parentComment->id,
      'reply_comment_id' => $this->reply->id,
      'actor_id' => $this->actor->id,
      'actor_name' => $this->actor->name,
      'actor_username' => $this->actor->username,
      'body_preview' => Str::limit($this->reply->body, 120),
    ];
  }
}
