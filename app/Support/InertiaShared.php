<?php
/**
 * Inertia 用の共有データ（件数キャッシュ・auth 用の公開フィールド）
 * @package App\Support
 */
namespace App\Support;

use App\Models\Band;
use App\Models\MerchItem;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

final class InertiaShared
{
  private const STATS_CACHE_KEY = 'inertia.ui.stats.v1';

  private const STATS_TTL_SECONDS = 60;

  public static function forgetGlobalStatsCache(): void
  {
    Cache::forget(self::STATS_CACHE_KEY);
  }

  public static function cachedGlobalStats(): array
  {
    return Cache::remember(self::STATS_CACHE_KEY, self::STATS_TTL_SECONDS, function (): array {
      return [
        'bands' => Band::query()->count(),
        'merchItems' => MerchItem::query()->count(),
      ];
    });
  }

  // メール・トークンは載せない（プロフィールのメールは別途）
  public static function authUser(?User $user): ?array
  {
    if ($user === null) {
      return null;
    }

    return [
      'id' => $user->id,
      'name' => $user->name,
      'username' => $user->username,
      'bio' => $user->bio,
      'avatar_path' => $user->avatar_path,
      'avatar_focus_x' => $user->avatar_focus_x,
      'avatar_focus_y' => $user->avatar_focus_y,
      'avatar_zoom' => $user->avatar_zoom,
      'role' => $user->role,
    ];
  }
}
