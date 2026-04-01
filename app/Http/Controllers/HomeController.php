<?php

namespace App\Http\Controllers;

use App\Models\Band;
use App\Models\MerchItem;
use App\Models\Post;
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
        'posts' => Post::query()
          ->visibleOnFeed()
          ->with(['user:id,name,username', 'band:id,name,slug', 'coverImage:id,post_id,image_path'])
          ->latest('published_at')
          ->limit(4)
          ->get(['id', 'user_id', 'band_id', 'body', 'published_at']),
      ],
    ]);
  }
}
