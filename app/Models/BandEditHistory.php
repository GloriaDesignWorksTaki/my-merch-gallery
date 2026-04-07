<?php
/**
 * バンド編集履歴のモデル定義
 * @package App\Models
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BandEditHistory extends Model
{
  public $timestamps = false;

  protected $fillable = [
    'band_id',
    'user_id',
    'changes',
  ];
  protected function casts(): array
  {
    return [
      'changes' => 'array',
      'created_at' => 'datetime',
    ];
  }
  public function band(): BelongsTo
  {
    return $this->belongsTo(Band::class);
  }
  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }
}
