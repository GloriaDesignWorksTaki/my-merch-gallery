<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MerchImage extends Model
{
  protected $fillable = [
    'merch_item_id',
    'image_path',
    'alt_text',
    'sort_order',
  ];

  public function merchItem(): BelongsTo
  {
    return $this->belongsTo(MerchItem::class);
  }
}
