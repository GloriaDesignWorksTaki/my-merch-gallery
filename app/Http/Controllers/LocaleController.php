<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $supported = implode(',', config('i18n.supported', ['en']));

        $validated = $request->validate([
            'locale' => 'required|string|in:'.$supported,
        ]);

        $request->session()->put('locale', $validated['locale']);

        return back();
    }
}
