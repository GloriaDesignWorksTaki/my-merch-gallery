<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Post extends Model
{
  protected $fillable = [
    'user_id',
    'band_id',
    'merch_item_id',
    'body',
    'visibility',
    'published_at',
  ];

  protected function casts(): array
  {
    return [
      'published_at' => 'datetime',
    ];
  }

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  public function band(): BelongsTo
  {
    return $this->belongsTo(Band::class);
  }

  public function merchItem(): BelongsTo
  {
    return $this->belongsTo(MerchItem::class);
  }

  public function images(): HasMany
  {
    return $this->hasMany(PostImage::class)->orderBy('sort_order');
  }

  public function coverImage(): HasOne
  {
    return $this->hasOne(PostImage::class)->orderBy('sort_order');
  }

  public function comments(): HasMany
  {
    return $this->hasMany(Comment::class);
  }

  public function likes(): HasMany
  {
    return $this->hasMany(PostLike::class);
  }

  public function scopeVisibleOnFeed(Builder $query): Builder
  {
    return $query->where('visibility', 'public');
  }

  public function scopeVisibleTo(Builder $query, ?User $viewer): Builder
  {
    return $query->where(function (Builder $builder) use ($viewer) {
      $builder->where('visibility', 'public')
        ->orWhere('visibility', 'unlisted');

      if ($viewer !== null) {
        $builder->orWhere(function (Builder $privateBuilder) use ($viewer) {
          $privateBuilder->where('visibility', 'private')
            ->where('user_id', $viewer->id);
        });
      }
    });
  }
}
