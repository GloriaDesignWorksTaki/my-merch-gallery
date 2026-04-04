<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MerchItemComment extends Model
{
    protected $fillable = [
        'merch_item_id',
        'parent_id',
        'user_id',
        'body',
    ];

    public function merchItem(): BelongsTo
    {
        return $this->belongsTo(MerchItem::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MerchItemComment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(MerchItemComment::class, 'parent_id')->orderBy('created_at');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(MerchItemCommentLike::class);
    }
}
