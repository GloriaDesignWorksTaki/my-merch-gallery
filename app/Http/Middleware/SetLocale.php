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
    $supported = config('i18n.supported', ['ja', 'en']);
    $default = config('app.locale', 'ja');
    $sessionLocale = $request->session()->get('locale');
    $userLocale = $request->user()?->preferred_locale;
    $locale = $userLocale ?? $sessionLocale ?? $default;

    if (! in_array($locale, $supported, true)) {
      $locale = $default;
    }

    $request->session()->put('locale', $locale);
    App::setLocale($locale);

    return $next($request);
  }
}
