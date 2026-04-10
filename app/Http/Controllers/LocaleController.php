<?php
/**
 * 表示言語の切り替え（セッション保存）
 * @package App\Http\Controllers
 */
namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
  public function __invoke(Request $request): RedirectResponse
  {
    $supportedList = config('i18n.supported', ['ja', 'en']);
    $supported = implode(',', $supportedList);

    $validated = $request->validate([
      'locale' => 'required|string|in:'.$supported,
    ]);

    $locale = $validated['locale'];

    $request->session()->put('locale', $locale);

    $user = $request->user();
    if ($user !== null && in_array($locale, $supportedList, true)) {
      $user->forceFill(['preferred_locale' => $locale])->save();
    }

    return back();
  }
}
