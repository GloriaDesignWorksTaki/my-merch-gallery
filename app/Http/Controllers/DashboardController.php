<?php
/**
 * ログイン後トップ（ダッシュボード）画面
 * @package App\Http\Controllers
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
  public function __invoke(Request $request): Response
  {
    $user = $request->user();

    return Inertia::render('Dashboard', [
      'summary' => [
        'bands' => $user->createdBands()->count(),
        'merchItems' => $user->createdMerchItems()->count(),
      ],
      'recentBands' => $user->createdBands()
        ->latest()
        ->limit(3)
        ->get(['id', 'name', 'slug']),
      'recentMerchItems' => $user->createdMerchItems()
        ->with(['band:id,name,slug', 'coverImage:id,merch_item_id,image_path,alt_text'])
        ->latest()
        ->limit(3)
        ->get(['id', 'band_id', 'name', 'slug']),
      'profileHints' => [
        'bioMissing' => blank($user->bio),
        'avatarMissing' => blank($user->avatar_path),
      ],
    ]);
  }
}
