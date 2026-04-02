<?php

namespace App\Support;

use App\Models\Band;
use App\Models\MerchItem;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * Inertia 全ページ共有プロップ用。フロントに載せる属性をここで明示し、PII の漏えいを防ぐ。
 */
final class InertiaShared
{
    /** サイドバー等の件数表示用。毎リクエスト DB を叩かない。 */
    private const STATS_CACHE_KEY = 'inertia.ui.stats.v1';

    private const STATS_TTL_SECONDS = 60;

    public static function forgetGlobalStatsCache(): void
    {
        Cache::forget(self::STATS_CACHE_KEY);
    }

    /**
     * @return array{bands: int, merchItems: int, posts: int}
     */
    public static function cachedGlobalStats(): array
    {
        return Cache::remember(self::STATS_CACHE_KEY, self::STATS_TTL_SECONDS, function (): array {
            return [
                'bands' => Band::query()->count(),
                'merchItems' => MerchItem::query()->count(),
                'posts' => Post::query()->count(),
            ];
        });
    }

    /**
     * メール・認証トークンは含めない。プロフィール編集のメールは ProfileController からページ専用で渡す。
     *
     * @return array<string, mixed>|null
     */
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
