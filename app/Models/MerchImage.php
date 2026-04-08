<?php
/**
 * マーチ画像のモデル定義
 * @package App\Models
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

class MerchImage extends Model
{
  protected $appends = [
    'image_url',
  ];

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

  protected function imageUrl(): Attribute
  {
    return Attribute::get(function (): ?string {
      if ($this->image_path === null || $this->image_path === '') {
        return null;
      }

      /** @var FilesystemAdapter $disk */
      $disk = Storage::disk('uploads');

      return $disk->url($this->image_path);
    });
  }
}
