<?php
/**
 * 起動時の横断設定
 * @package App\Providers
 */
namespace App\Providers;

use App\Models\Band;
use App\Models\MerchItem;
use App\Models\User;
use App\Support\InertiaShared;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
  public function register(): void
  {
    // 何もしない
  }
  public function boot(): void
  {
    // 表示を速くする
    Vite::prefetch(concurrency: 3);
    // 管理画面はスタッフのみ
    Gate::define('access-admin', fn (User $user): bool => $user->isStaff());
    // 件数表示を最新に保つ
    foreach ([Band::class, MerchItem::class] as $modelClass) {
      $modelClass::created(static fn () => InertiaShared::forgetGlobalStatsCache());
      $modelClass::deleted(static fn () => InertiaShared::forgetGlobalStatsCache());
    }
  }
}
