<?php
/**
 * ジャンルマスタのモデル定義
 * @package App\Models
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Genre extends Model
{
  protected $fillable = [
    'name',
    'slug',
  ];
  public function bands(): BelongsToMany
  {
    return $this->belongsToMany(Band::class)->withTimestamps();
  }
}
