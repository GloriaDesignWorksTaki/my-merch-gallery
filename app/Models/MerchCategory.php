<?php
/**
 * マーチカテゴリマスタのモデル定義
 * @package App\Models
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MerchCategory extends Model
{
  protected $fillable = [
    'name',
    'slug',
    'sort_order',
  ];
  public function merchItems(): HasMany
  {
    return $this->hasMany(MerchItem::class);
  }
}
