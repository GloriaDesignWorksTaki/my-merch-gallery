<?php
/**
 * マーチコメントいいねのモデル定義
 * @package App\Models
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MerchItemCommentLike extends Model
{
  protected $fillable = [
    'merch_item_comment_id',
    'user_id',
  ];
  public function comment(): BelongsTo
  {
    return $this->belongsTo(MerchItemComment::class, 'merch_item_comment_id');
  }
  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }
}
