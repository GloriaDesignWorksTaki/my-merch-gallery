<?php
/**
 * セッションの言語を Laravel に反映（HandleInertia より前）
 * @package App\Http\Middleware
 */
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
  public function handle(Request $request, Closure $next): Response
  {
    $supported = config('i18n.supported', ['en']);
    $default = config('app.locale', 'en');

    $locale = $request->session()->get('locale', $default);

    if (! in_array($locale, $supported, true)) {
      $locale = $default;
    }

    App::setLocale($locale);

    return $next($request);
  }
}
