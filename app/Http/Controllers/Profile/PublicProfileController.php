<?php
/**
 * 公開ユーザープロフィール（他者向け）
 * @package App\Http\Controllers\Profile
 */
namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Models\Band;
use App\Models\MerchItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PublicProfileController extends Controller
{
  public function show(Request $request, User $user): Response
  {
  $tab = $request->string('tab')->toString();
  $allowedTabs = ['posts', 'likedBands', 'likedMerch'];
  if (! in_array($tab, $allowedTabs, true)) {
    $tab = 'posts';
  }

  $user->loadCount(['createdMerchItems', 'bandLikes', 'merchItemLikes']);
  $viewer = Auth::user();

  return Inertia::render('Users/Show', [
  'profileUser' => [
    'id' => $user->id,
    'name' => $user->name,
    'username' => $user->username,
    'bio' => $user->bio,
    'avatar_path' => $user->avatar_path,
    'avatar_url' => $user->avatar_url,
    'avatar_focus_x' => $user->avatar_focus_x,
    'avatar_focus_y' => $user->avatar_focus_y,
    'avatar_zoom' => $user->avatar_zoom,
    'created_merch_items_count' => $user->created_merch_items_count,
    'liked_bands_count' => $user->band_likes_count,
    'liked_merch_count' => $user->merch_item_likes_count,
  ],
  'tab' => $tab,
  'postedMerchItems' => $tab === 'posts'
    ? MerchItem::query()
      ->select(['id', 'band_id', 'merch_category_id', 'name', 'slug', 'release_year', 'is_official'])
      ->with([
        'band:id,name,slug',
        'category:id,name',
        'coverImage:id,merch_item_id,image_path,alt_text',
      ])
      ->withCount('likes')
      ->when($viewer !== null, function ($q) use ($viewer) {
        $q->withExists(['likes as liked' => function ($q) use ($viewer) {
          $q->where('user_id', $viewer->id);
        }]);
      })
      ->where('created_by', $user->id)
      ->latest()
      ->paginate(12)
      ->withQueryString()
    : null,
  'likedBands' => $tab === 'likedBands'
    ? Band::query()
      ->select(['bands.id', 'bands.name', 'bands.slug', 'bands.image_path', 'bands.country_id'])
      ->with(['country:id,name'])
      ->withCount(['merchItems', 'likes'])
      ->when($viewer !== null, function ($q) use ($viewer) {
        $q->withExists(['likes as liked' => function ($q) use ($viewer) {
          $q->where('user_id', $viewer->id);
        }]);
      })
      ->join('band_likes', 'band_likes.band_id', '=', 'bands.id')
      ->where('band_likes.user_id', $user->id)
      ->orderByDesc('band_likes.created_at')
      ->paginate(20)
      ->withQueryString()
    : null,
  'likedMerchItems' => $tab === 'likedMerch'
    ? MerchItem::query()
      ->select(['merch_items.id', 'merch_items.band_id', 'merch_items.merch_category_id', 'merch_items.name', 'merch_items.slug', 'merch_items.release_year', 'merch_items.is_official'])
      ->with([
        'band:id,name,slug',
        'category:id,name',
        'coverImage:id,merch_item_id,image_path,alt_text',
      ])
      ->withCount('likes')
      ->when($viewer !== null, function ($q) use ($viewer) {
        $q->withExists(['likes as liked' => function ($q) use ($viewer) {
          $q->where('user_id', $viewer->id);
        }]);
      })
      ->join('merch_item_likes', 'merch_item_likes.merch_item_id', '=', 'merch_items.id')
      ->where('merch_item_likes.user_id', $user->id)
      ->orderByDesc('merch_item_likes.created_at')
      ->paginate(12)
      ->withQueryString()
    : null,
  ]);
  }
}
