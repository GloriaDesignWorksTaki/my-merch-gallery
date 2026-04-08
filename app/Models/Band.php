<?php
/**
 * バンドのモデル定義
 * @package App\Models
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Band extends Model
{
  protected $appends = [
    'image_url',
  ];

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
    'image_path',
  ];

  protected static function booted(): void
  {
    static::creating(function (Band $band): void {
      if (blank($band->uuid)) {
        $band->uuid = (string) Str::uuid();
      }
    });
  }
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
  public function likes(): HasMany
  {
    return $this->hasMany(BandLike::class);
  }
  public function editHistories(): HasMany
  {
    return $this->hasMany(BandEditHistory::class)->latest('created_at');
  }
  public function editRequests(): HasMany
  {
    return $this->hasMany(BandEditRequest::class);
  }
  public function getRouteKeyName(): string
  {
    return 'slug';
  }
  // 表示・ソート用（先頭の The を除くなど）
  public static function normalizeSortName(string $name): string
  {
    return Str::of($name)
      ->lower()
      ->replaceMatches('/^the\s+/i', '')
      ->squish()
      ->toString();
  }
  // 重複チェック用（ハイフン種別・空白を揃える）
  public static function normalizeComparableName(string $name): string
  {
    return Str::of($name)
      ->lower()
      ->replaceMatches('/[‐‑‒–—―]+/u', '-')
      ->replaceMatches('/\s+/', ' ')
      ->trim()
      ->toString();
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
