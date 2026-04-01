<?php

namespace App\Http\Middleware;

use App\Models\Band;
use App\Models\MerchItem;
use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
  /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
  protected $rootView = 'app';

  /**
     * Determine the current asset version.
     */
  public function version(Request $request): ?string
  {
    return parent::version($request);
  }

  /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
  public function share(Request $request): array
  {
    return [
      ...parent::share($request),
      'auth' => [
        'user' => $request->user(),
      ],
      'flash' => [
        'status' => fn () => $request->session()->get('status'),
      ],
      'ui' => [
        'stats' => [
          'bands' => fn () => Band::query()->count(),
          'merchItems' => fn () => MerchItem::query()->count(),
          'posts' => fn () => Post::query()->count(),
        ],
      ],
    ];
  }
}
