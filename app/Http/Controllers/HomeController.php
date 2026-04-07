<?php
/**
 * ゲスト向けホーム（おすすめ表示）
 * @package App\Http\Controllers
 */
namespace App\Http\Controllers;

use App\Models\Band;
use App\Models\MerchItem;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
  public function __invoke(): Response
  {
    return Inertia::render('Home/Index', [
      'featured' => [
        'bands' => Band::query()->orderByDesc('id')->limit(4)->get(['id', 'name', 'slug']),
        'merchItems' => MerchItem::query()
          ->with(['band:id,name,slug', 'coverImage:id,merch_item_id,image_path,alt_text'])
          ->latest()
          ->limit(4)
          ->get(['id', 'band_id', 'name', 'slug']),
      ],
    ]);
  }
}
