<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BandLink extends Model
{
  protected $fillable = [
    'band_id',
    'url',
    'sort_order',
  ];

  public function band(): BelongsTo
  {
    return $this->belongsTo(Band::class);
  }
}
