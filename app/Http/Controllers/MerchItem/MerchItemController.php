<?php
/**
 * マーチの一覧・詳細・作成・更新・画像
 * @package App\Http\Controllers\MerchItem
 */
namespace App\Http\Controllers\MerchItem;

use App\Http\Controllers\Controller;
use App\Http\Requests\MerchItem\StoreMerchItemRequest;
use App\Http\Requests\MerchItem\UpdateMerchItemRequest;
use App\Models\Band;
use App\Models\MerchCategory;
use App\Models\MerchItem;
use App\Support\BuildsUniqueSlug;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class MerchItemController extends Controller
{
  public function index(Request $request): Response
  {
  $search = trim((string) $request->string('search')->toString());
  $bandIds = $this->bandIdsFromRequest($request);
  $selectedCategory = $request->integer('category');
  $sort = $request->string('sort')->toString();
  $allowedSorts = ['newest', 'oldest', 'name'];

  if (! in_array($sort, $allowedSorts, true)) {
  $sort = 'newest';
  }

  $query = MerchItem::query()
  ->select(['id', 'band_id', 'merch_category_id', 'name', 'slug', 'release_year', 'is_official'])
  ->with([
    'band:id,name,slug',
    'category:id,name',
    'coverImage:id,merch_item_id,image_path,alt_text',
  ])
  ->withCount('likes');

  if (Auth::check()) {
  $query->withExists(['likes as liked' => function ($q) {
    $q->where('user_id', Auth::id());
  }]);
  }

  if ($search !== '') {
  $query->where('name', 'like', '%'.$search.'%');
  }

  if (count($bandIds) > 0) {
  $query->whereIn('band_id', $bandIds);
  }

  if ($selectedCategory > 0) {
  $query->where('merch_category_id', $selectedCategory);
  }

  match ($sort) {
  'oldest' => $query->orderBy('created_at')->orderBy('id'),
  'name' => $query->orderBy('name')->orderByDesc('created_at'),
  default => $query->latest(),
  };

  return Inertia::render('MerchItems/Index', [
  'merchItems' => $query
    ->paginate(24)
    ->withQueryString(),
  'filters' => [
    'search' => $search,
    'bands' => $bandIds,
    'category' => $selectedCategory > 0 ? $selectedCategory : null,
    'sort' => $sort,
  ],
  'bands' => Band::query()->orderBy('name')->get(['id', 'name']),
  'categories' => MerchCategory::query()->orderBy('sort_order')->get(['id', 'name']),
  ]);
  }

  public function create(Request $request): Response
  {
  $selectedBandId = Band::query()
  ->whereKey($request->integer('band'))
  ->value('id');

  return Inertia::render('MerchItems/Create', [
  'bands' => Band::query()->orderBy('name')->get(['id', 'name']),
  'categories' => MerchCategory::query()->orderBy('sort_order')->get(['id', 'name']),
  'selectedBandId' => $selectedBandId,
  ]);
  }

  public function store(StoreMerchItemRequest $request): RedirectResponse
  {
  $payload = $request->validated();

  $merchItem = DB::transaction(function () use ($payload, $request) {
  $merchItem = MerchItem::create([
    'band_id' => $payload['band_id'],
    'merch_category_id' => $payload['merch_category_id'],
    'created_by' => $request->user()->id,
    'name' => $payload['name'],
    'slug' => BuildsUniqueSlug::for(new MerchItem, $payload['name']),
    'description' => $payload['description'] ?? null,
    'release_year' => $payload['release_year'] ?? null,
    'size_note' => filled($payload['size_note'] ?? null) ? $payload['size_note'] : null,
    'is_official' => $payload['is_official'],
    'source_type' => $payload['source_type'],
  ]);

  $this->syncImages($merchItem, $request->file('images', []));

  return $merchItem;
  });

  return redirect()->route('merch-items.show', $merchItem)->with('status', 'merch-item-created');
  }

  public function show(Request $request, MerchItem $merchItem): Response
  {
  $viewer = Auth::user();

  $merchItem->load([
    'band',
    'category',
    'images',
    'creator:id,name,username,avatar_path,avatar_focus_x,avatar_focus_y,avatar_zoom',
  ]);
  $merchItem->loadCount('likes');

  if ($viewer !== null) {
  $merchItem->loadExists(['likes as liked' => function ($q) use ($viewer) {
    $q->where('user_id', $viewer->id);
  }]);
  }

  $relatedMerchQuery = MerchItem::query()
  ->select(['id', 'band_id', 'name', 'slug', 'release_year', 'is_official'])
  ->with('coverImage:id,merch_item_id,image_path,alt_text')
  ->where('band_id', $merchItem->band_id)
  ->whereKeyNot($merchItem->id)
  ->withCount('likes')
  ->orderBy('release_year')
  ->orderBy('name')
  ->limit(4);

  if ($viewer !== null) {
  $relatedMerchQuery->withExists(['likes as liked' => function ($q) use ($viewer) {
    $q->where('user_id', $viewer->id);
  }]);
  }

  $relatedMerchItems = $relatedMerchQuery->get();

  $commentsQuery = $merchItem->comments()
  ->whereNull('parent_id')
  ->withCount('likes')
  ->with([
    'user:id,name,username,avatar_path,avatar_focus_x,avatar_focus_y,avatar_zoom',
    'replies' => function ($q) use ($viewer) {
    $q->withCount('likes')
      ->with('user:id,name,username,avatar_path,avatar_focus_x,avatar_focus_y,avatar_zoom')
      ->orderBy('created_at');
    if ($viewer !== null) {
      $q->withExists(['likes as liked' => function ($q) use ($viewer) {
      $q->where('user_id', $viewer->id);
      }]);
    }
    },
  ]);

  if ($viewer !== null) {
  $commentsQuery->withExists(['likes as liked' => function ($q) use ($viewer) {
    $q->where('user_id', $viewer->id);
  }]);
  }

  $comments = $commentsQuery->latest()->paginate(20)->withQueryString();

  return Inertia::render('MerchItems/Show', [
  'merchItem' => $merchItem,
  'canEdit' => $viewer !== null && Gate::allows('update', $merchItem),
  'returnTo' => route('merch-items.index', $this->merchIndexQueryForReturn($request)),
  'relatedMerchItems' => $relatedMerchItems,
  'comments' => $comments,
  ]);
  }

  public function options(Band $band): JsonResponse
  {
  return response()->json([
  'merchItems' => $band->merchItems()
    ->select(['id', 'name'])
    ->orderBy('name')
    ->get(),
  ]);
  }

  public function edit(Request $request, MerchItem $merchItem): Response
  {
  $this->authorize('update', $merchItem);
  $merchItem->load('images:id,merch_item_id,image_path,alt_text,sort_order');

  return Inertia::render('MerchItems/Edit', [
  'merchItem' => $merchItem,
  'bands' => Band::query()->orderBy('name')->get(['id', 'name']),
  'categories' => MerchCategory::query()->orderBy('sort_order')->get(['id', 'name']),
  'returnTo' => (string) ($request->query('return_to') ?: route('merch-items.show', $merchItem)),
  ]);
  }

  public function update(UpdateMerchItemRequest $request, MerchItem $merchItem): RedirectResponse
  {
  $this->authorize('update', $merchItem);

  $payload = $request->validated();

  DB::transaction(function () use ($merchItem, $payload, $request) {
  $merchItem->update([
    'band_id' => $payload['band_id'],
    'merch_category_id' => $payload['merch_category_id'],
    'name' => $payload['name'],
    'slug' => BuildsUniqueSlug::for($merchItem, $payload['name'], $merchItem->id),
    'description' => $payload['description'] ?? null,
    'release_year' => $payload['release_year'] ?? null,
    'size_note' => filled($payload['size_note'] ?? null) ? $payload['size_note'] : null,
    'is_official' => $payload['is_official'],
    'source_type' => $payload['source_type'],
  ]);

  $this->syncImages(
    $merchItem,
    $request->file('images', []),
    collect($payload['existing_image_ids'] ?? [])->map(fn ($id) => (int) $id)->all(),
  );
  });

  return redirect()->route('merch-items.show', $merchItem)->with('status', 'merch-item-updated');
  }

  // 新規アップロードと、残さない画像の削除
  protected function syncImages(MerchItem $merchItem, array $images, array $existingImageIds = []): void
  {
  $merchItem->loadMissing('images');

  $imagesToDelete = $merchItem->images
  ->when($existingImageIds !== [], fn ($collection) => $collection->whereNotIn('id', $existingImageIds), fn ($collection) => $collection);

  foreach ($imagesToDelete as $image) {
  Storage::disk('uploads')->delete($image->image_path);
  }

  if ($imagesToDelete->isNotEmpty()) {
  $merchItem->images()->whereIn('id', $imagesToDelete->pluck('id'))->delete();
  }

  $startingSortOrder = (int) $merchItem->images()
  ->max('sort_order');

  foreach ($images as $index => $image) {
  $path = $image->store('merch-items', 'uploads');

  $merchItem->images()->create([
    'image_path' => $path,
    'sort_order' => $startingSortOrder + $index + 1,
  ]);
  }
  }
}
