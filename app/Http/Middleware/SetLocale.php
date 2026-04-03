<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * セッション（または既定）のロケールを Laravel に反映する。
     * HandleInertiaRequests より前に実行すること。
     */
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
