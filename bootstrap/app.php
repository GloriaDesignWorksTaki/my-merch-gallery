<?php

use App\Http\Middleware\EnsureUserIsNotBanned;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
  ->withRouting(
    web: __DIR__.'/../routes/web.php',
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
    then: function () {
      Route::middleware('web')
        ->group(base_path('routes/admin.php'));
    },
  )
  ->withMiddleware(function (Middleware $middleware): void {
    // Render/Cloudflare 経由の X-Forwardedを信頼してHTTPSを正しく判定する
    $middleware->trustProxies(
      at: '*',
      headers: Request::HEADER_X_FORWARDED_FOR
        | Request::HEADER_X_FORWARDED_PORT
        | Request::HEADER_X_FORWARDED_PROTO
        | Request::HEADER_X_FORWARDED_AWS_ELB,
    );
    $middleware->web(append: [
      SetLocale::class,
      HandleInertiaRequests::class,
      AddLinkHeadersForPreloadedAssets::class,
      EnsureUserIsNotBanned::class,
    ]);
  })
  ->withExceptions(function (Exceptions $exceptions): void {
  })->create();
