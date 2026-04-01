<?php

namespace App\Http\Controllers;

use App\Models\MerchItem;
use App\Models\Post;
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
        'posts' => $user->posts()->count(),
      ],
      'recentBands' => $user->createdBands()
        ->latest()
        ->limit(3)
        ->get(['id', 'name', 'slug']),
      'recentMerchItems' => $user->createdMerchItems()
        ->with('band:id,name,slug')
        ->latest()
        ->limit(3)
        ->get(['id', 'band_id', 'name', 'slug']),
      'recentPosts' => Post::query()
        ->where('user_id', $user->id)
        ->with(['band:id,name,slug', 'coverImage:id,post_id,image_path'])
        ->latest()
        ->limit(3)
        ->get(['id', 'band_id', 'body', 'published_at']),
      'profileHints' => [
        'bioMissing' => blank($user->bio),
        'avatarMissing' => blank($user->avatar_path),
      ],
    ]);
  }
}
