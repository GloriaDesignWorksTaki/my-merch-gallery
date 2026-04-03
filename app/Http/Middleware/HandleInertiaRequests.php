<?php

namespace App\Http\Middleware;

use App\Support\InertiaShared;
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
            'locale' => fn () => app()->getLocale(),
            'locales' => fn () => collect(config('i18n.supported', ['en']))
                ->map(fn (string $code) => [
                    'code' => $code,
                    'label' => config('i18n.labels')[$code] ?? strtoupper($code),
                ])
                ->values()
                ->all(),
            'auth' => [
                'user' => fn () => InertiaShared::authUser($request->user()),
            ],
            'flash' => [
                'status' => fn () => $request->session()->get('status'),
            ],
            'ui' => [
                'stats' => fn () => InertiaShared::cachedGlobalStats(),
            ],
        ];
    }
}
