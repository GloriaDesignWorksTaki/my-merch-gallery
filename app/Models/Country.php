<?php
/**
 * 国マスタのモデル定義
 * @package App\Models
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
  protected $fillable = [
    'name',
    'iso_code',
  ];
  public function bands(): HasMany
  {
    return $this->hasMany(Band::class);
  }
}
