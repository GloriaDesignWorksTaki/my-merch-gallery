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
    $supported = implode(',', config('i18n.supported', ['en']));

    $validated = $request->validate([
      'locale' => 'required|string|in:'.$supported,
    ]);

    $request->session()->put('locale', $validated['locale']);

    return back();
  }
}
