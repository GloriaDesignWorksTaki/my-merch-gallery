<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Band extends Model
{
  protected $fillable = [
    'created_by',
    'musicbrainz_id',
    'name',
    'slug',
    'sort_name',
    'country_id',
    'description',
    'formed_year',
    'is_active',
  ];

  protected function casts(): array
  {
    return [
      'is_active' => 'boolean',
    ];
  }

  public function creator(): BelongsTo
  {
    return $this->belongsTo(User::class, 'created_by');
  }

  public function country(): BelongsTo
  {
    return $this->belongsTo(Country::class);
  }

  public function genres(): BelongsToMany
  {
    return $this->belongsToMany(Genre::class)->withTimestamps();
  }

  public function links(): HasMany
  {
    return $this->hasMany(BandLink::class)->orderBy('sort_order');
  }

  public function merchItems(): HasMany
  {
    return $this->hasMany(MerchItem::class);
  }

  public function posts(): HasMany
  {
    return $this->hasMany(Post::class);
  }

  public function getRouteKeyName(): string
  {
    return 'slug';
  }

  public static function normalizeSortName(string $name): string
  {
    return Str::of($name)
      ->lower()
      ->replaceMatches('/^the\s+/i', '')
      ->squish()
      ->toString();
  }

  public static function normalizeComparableName(string $name): string
  {
    return Str::of($name)
      ->lower()
      ->replaceMatches('/[‐‑‒–—―]+/u', '-')
      ->replaceMatches('/\s+/', ' ')
      ->trim()
      ->toString();
  }
}
