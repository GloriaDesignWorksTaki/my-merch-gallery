<?php

namespace App\Http\Controllers\Post;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Models\Band;
use App\Models\MerchItem;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
  public function index(Request $request): Response
  {
    $search = trim((string) $request->string('search')->toString());
    $selectedBand = $request->integer('band');
    $visibility = $request->string('visibility')->toString();
    $sort = $request->string('sort')->toString();
    $allowedVisibility = ['public', 'unlisted'];
    $allowedSorts = ['newest', 'oldest'];

    if (! in_array($sort, $allowedSorts, true)) {
      $sort = 'newest';
    }

    $query = Post::query()
      ->select(['id', 'user_id', 'band_id', 'merch_item_id', 'body', 'visibility', 'published_at'])
      ->visibleTo(Auth::user())
      ->with([
        'user:id,name,username',
        'band:id,name,slug',
        'merchItem:id,name,slug',
        'coverImage:id,post_id,image_path',
      ]);

    if ($search !== '') {
      $query->where('body', 'like', '%'.$search.'%');
    }

    if ($selectedBand > 0) {
      $query->where('band_id', $selectedBand);
    }

    if (in_array($visibility, $allowedVisibility, true)) {
      $query->where('visibility', $visibility);
    }

    match ($sort) {
      'oldest' => $query->orderBy('published_at')->orderBy('id'),
      default => $query->latest('published_at')->latest(),
    };

    return Inertia::render('Posts/Index', [
      'posts' => $query->paginate(24)->withQueryString(),
      'filters' => [
        'search' => $search,
        'band' => $selectedBand > 0 ? $selectedBand : null,
        'visibility' => in_array($visibility, $allowedVisibility, true) ? $visibility : null,
        'sort' => $sort,
      ],
      'bands' => Band::query()->orderBy('name')->get(['id', 'name']),
    ]);
  }

  public function create(): Response
  {
    return Inertia::render('Posts/Create', [
      'bands' => Band::query()->orderBy('name')->get(['id', 'name']),
    ]);
  }

  public function store(StorePostRequest $request): RedirectResponse
  {
    $payload = $request->validated();

    $post = DB::transaction(function () use ($payload, $request) {
      $post = Post::create([
        'user_id' => $request->user()->id,
        'band_id' => $payload['band_id'],
        'merch_item_id' => $payload['merch_item_id'] ?? null,
        'body' => $payload['body'],
        'visibility' => $payload['visibility'],
        'published_at' => now(),
      ]);

      $this->syncImages($post, $request->file('images', []));

      return $post;
    });

    return redirect()->route('posts.show', $post)->with('status', 'post-created');
  }

  public function show(Request $request, Post $post): Response
  {
    $viewer = Auth::user();

    if (
      $post->visibility === 'private'
      && (
        $viewer === null
        || ((int) $viewer->id !== (int) $post->user_id && $viewer->role !== 'admin')
      )
    ) {
      throw new HttpException(403);
    }

    $post->load([
      'user:id,name,username,bio,avatar_path',
      'band:id,name,slug',
      'merchItem:id,name,slug,merch_category_id',
      'merchItem.category:id,name',
      'images',
    ]);
    $comments = $post->comments()
      ->select(['id', 'post_id', 'user_id', 'body', 'created_at'])
      ->with('user:id,name,username')
      ->latest()
      ->paginate(20)
      ->withQueryString();
    $relatedPosts = Post::query()
      ->select(['id', 'user_id', 'band_id', 'merch_item_id', 'body', 'published_at', 'visibility'])
      ->visibleTo($viewer)
      ->where('band_id', $post->band_id)
      ->whereKeyNot($post->id)
      ->with(['user:id,name,username', 'coverImage:id,post_id,image_path'])
      ->latest('published_at')
      ->limit(4)
      ->get();
    $relatedMerchItems = MerchItem::query()
      ->select(['id', 'band_id', 'merch_category_id', 'name', 'slug', 'release_year', 'is_official'])
      ->with(['category:id,name', 'coverImage:id,merch_item_id,image_path,alt_text'])
      ->where('band_id', $post->band_id)
      ->orderBy('name')
      ->limit(4)
      ->get();

    return Inertia::render('Posts/Show', [
      'post' => $post,
      'comments' => $comments,
      'canEdit' => Auth::check() && Gate::allows('update', $post),
      'returnTo' => route('posts.index', array_filter([
        'page' => $request->query('page'),
        'search' => $request->query('search'),
        'band' => $request->query('band'),
        'visibility' => $request->query('visibility'),
        'sort' => $request->query('sort'),
      ], fn ($value) => filled($value))),
      'relatedPosts' => $relatedPosts,
      'relatedMerchItems' => $relatedMerchItems,
    ]);
  }

  public function edit(Request $request, Post $post): Response
  {
    $this->authorize('update', $post);
    $post->load('images:id,post_id,image_path,sort_order');

    return Inertia::render('Posts/Edit', [
      'post' => $post,
      'bands' => Band::query()->orderBy('name')->get(['id', 'name']),
      'merchItems' => $post->band_id === null
        ? []
        : MerchItem::query()
          ->where('band_id', $post->band_id)
          ->orderBy('name')
          ->get(['id', 'band_id', 'name']),
      'returnTo' => (string) ($request->query('return_to') ?: route('posts.show', $post)),
    ]);
  }

  public function update(UpdatePostRequest $request, Post $post): RedirectResponse
  {
    $this->authorize('update', $post);
    $payload = $request->validated();

    DB::transaction(function () use ($post, $payload, $request) {
      $post->update([
        'band_id' => $payload['band_id'],
        'merch_item_id' => $payload['merch_item_id'] ?? null,
        'body' => $payload['body'],
        'visibility' => $payload['visibility'],
      ]);

      if ($request->hasFile('images')) {
        $this->syncImages($post, $request->file('images', []), true);
      }
    });

    return redirect()->route('posts.show', $post)->with('status', 'post-updated');
  }

  /**
   * @param  array<int, UploadedFile>  $images
   */
  protected function syncImages(Post $post, array $images, bool $replaceExisting = false): void
  {
    if ($replaceExisting) {
      foreach ($post->images as $image) {
        Storage::disk('public')->delete($image->image_path);
      }

      $post->images()->delete();
    }

    foreach ($images as $index => $image) {
      $path = $image->store('posts', 'public');

      $post->images()->create([
        'image_path' => $path,
        'sort_order' => $index + 1,
      ]);
    }
  }
}
