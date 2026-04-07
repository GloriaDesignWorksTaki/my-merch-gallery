<?php
/**
 * モデル内で重複しない slug を生成
 * @package App\Support
 */
namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BuildsUniqueSlug
{
  public static function for(Model $model, string $source, ?int $ignoreId = null): string
  {
    $base = Str::slug($source);
    $base = $base !== '' ? $base : 'item';
    $slug = $base;
    $suffix = 2;

    while (static::exists($model, $slug, $ignoreId)) {
      $slug = $base.'-'.$suffix;
      $suffix++;
    }

    return $slug;
  }

  protected static function exists(Model $model, string $slug, ?int $ignoreId = null): bool
  {
    $query = $model->newQuery()->where('slug', $slug);

    if ($ignoreId !== null) {
      $query->whereKeyNot($ignoreId);
    }

    return $query->exists();
  }
}
