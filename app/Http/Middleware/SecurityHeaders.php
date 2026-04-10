<?php
/**
 * ブラウザ向けのセキュリティ関連 HTTP ヘッダーを付与する
 *
 * @package App\Http\Middleware
 */
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
  public function handle(Request $request, Closure $next): Response
  {
    $response = $next($request);

    $response->headers->set('X-Content-Type-Options', 'nosniff');
    $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
    $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
    $response->headers->set(
      'Permissions-Policy',
      'camera=(), microphone=(), geolocation=(), payment=()',
    );

    if (app()->environment('production') && $request->secure()) {
      $response->headers->set(
        'Strict-Transport-Security',
        'max-age=31536000; includeSubDomains',
      );
    }

    return $response;
  }
}
