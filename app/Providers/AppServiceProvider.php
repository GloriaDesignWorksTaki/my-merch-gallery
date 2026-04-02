<?php

namespace App\Providers;

use App\Models\Band;
use App\Models\MerchItem;
use App\Models\Post;
use App\Models\User;
use App\Support\InertiaShared;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        Gate::define('access-admin', fn (User $user): bool => $user->role === 'admin');

        foreach ([Band::class, MerchItem::class, Post::class] as $modelClass) {
            $modelClass::created(static fn () => InertiaShared::forgetGlobalStatsCache());
            $modelClass::deleted(static fn () => InertiaShared::forgetGlobalStatsCache());
        }
    }
}
