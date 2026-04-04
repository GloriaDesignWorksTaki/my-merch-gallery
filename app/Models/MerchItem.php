<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MerchItem extends Model
{
    protected $fillable = [
        'band_id',
        'merch_category_id',
        'created_by',
        'name',
        'slug',
        'description',
        'release_year',
        'size_note',
        'is_official',
        'source_type',
    ];

    protected function casts(): array
    {
        return [
            'is_official' => 'boolean',
        ];
    }

    public function band(): BelongsTo
    {
        return $this->belongsTo(Band::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MerchCategory::class, 'merch_category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function images(): HasMany
    {
        return $this->hasMany(MerchImage::class)->orderBy('sort_order');
    }

    public function coverImage(): HasOne
    {
        return $this->hasOne(MerchImage::class)->orderBy('sort_order');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(MerchItemLike::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(MerchItemComment::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
