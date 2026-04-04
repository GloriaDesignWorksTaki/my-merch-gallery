<?php

namespace App\Notifications;

use App\Models\MerchItem;
use App\Models\MerchItemComment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class MerchItemCommentedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public MerchItem $merchItem,
        public MerchItemComment $comment,
        public User $actor,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'merch_item_commented',
            'merch_item_id' => $this->merchItem->id,
            'merch_item_slug' => $this->merchItem->slug,
            'merch_item_name' => $this->merchItem->name,
            'comment_id' => $this->comment->id,
            'actor_id' => $this->actor->id,
            'actor_name' => $this->actor->name,
            'actor_username' => $this->actor->username,
            'body_preview' => Str::limit($this->comment->body, 120),
        ];
    }
}
