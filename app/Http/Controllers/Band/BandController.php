<?php
/**
 * バンドの一覧・詳細・作成・更新
 * @package App\Http\Controllers\Band
 */
namespace App\Http\Controllers\Band;

use App\Http\Controllers\Controller;
use App\Http\Requests\Band\StoreBandRequest;
use App\Http\Requests\Band\UpdateBandRequest;
use App\Models\Band;
use App\Models\BandEditRequest;
use App\Models\Country;
use App\Models\Genre;
use App\Support\BandUpdater;
use App\Support\BuildsUniqueSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class BandController extends Controller
{
  public function index(Request $request): Response
  {
  $selectedLetter = strtoupper((string) $request->string('letter')->toString());
  $selectedLetter = preg_match('/^[A-Z]$/', $selectedLetter) === 1 || $selectedLetter === '#'
  ? $selectedLetter
  : '';

  $query = Band::query()
  ->select(['id', 'uuid', 'name', 'slug', 'country_id', 'image_path'])
  ->with('country:id,name')
  ->withCount(['merchItems', 'likes']);

  if (Auth::check()) {
  $query->withExists(['likes as liked' => function ($q) {
    $q->where('user_id', Auth::id());
  }]);
  }

  if ($selectedLetter !== '') {
  if ($selectedLetter === '#') {
    $query->whereRaw("UPPER(SUBSTRING(sort_name, 1, 1)) < 'A' OR UPPER(SUBSTRING(sort_name, 1, 1)) > 'Z'");
  } else {
    $query->whereRaw('UPPER(SUBSTRING(sort_name, 1, 1)) = ?', [$selectedLetter]);
  }
  }

  $availableLetters = Band::query()
  ->selectRaw("DISTINCT CASE WHEN UPPER(SUBSTRING(sort_name, 1, 1)) BETWEEN 'A' AND 'Z' THEN UPPER(SUBSTRING(sort_name, 1, 1)) ELSE '#' END AS letter")
  ->pluck('letter')
  ->filter(fn (?string $letter) => $letter !== null && $letter !== '')
  ->sort()
  ->values();

  return Inertia::render('Bands/Index', [
  'bands' => $query
    ->orderBy('sort_name')
    ->orderBy('name')
    ->paginate(24)
    ->withQueryString(),
  'selectedLetter' => $selectedLetter,
  'availableLetters' => $availableLetters,
  ]);
  }

  public function create(): Response
  {
  return Inertia::render('Bands/Create', [
  'countries' => Country::query()->orderBy('name')->get(['id', 'name']),
  'genres' => Genre::query()->orderBy('name')->get(['id', 'name']),
  ]);
  }

  public function store(StoreBandRequest $request): RedirectResponse
  {
  $payload = $request->validated();

  $band = DB::transaction(function () use ($payload, $request) {
  $band = Band::create([
    'created_by' => $request->user()->id,
    'name' => $payload['name'],
    'slug' => BuildsUniqueSlug::for(new Band, $payload['name']),
    'sort_name' => Band::normalizeSortName($payload['name']),
    'country_id' => $payload['country_id'] ?? null,
    'description' => $payload['description'] ?? null,
    'formed_year' => $payload['formed_year'] ?? null,
    'is_active' => $payload['is_active'],
  ]);

  $band->genres()->sync($payload['genre_ids'] ?? []);
  BandUpdater::syncLinks($band, $payload['links'] ?? []);

  if ($request->hasFile('image')) {
    $path = $request->file('image')->store('bands', 'uploads');
    $band->update(['image_path' => $path]);
  }

  return $band;
  });

  return redirect()->route('bands.show', $band)->with('status', 'band-created');
  }

  public function show(Request $request, Band $band): Response
  {
  $viewer = Auth::user();

  $band->load([
  'creator:id,name,username',
  'country:id,name',
  'genres:id,name',
  'links:id,band_id,url,sort_order',
  ]);
  $band->loadCount('likes');

  if ($viewer !== null) {
  $band->loadExists(['likes as liked' => function ($q) use ($viewer) {
    $q->where('user_id', $viewer->id);
  }]);
  }

  $merchItemsQuery = $band->merchItems()
  ->select(['id', 'band_id', 'merch_category_id', 'name', 'slug', 'release_year', 'is_official'])
  ->with('category:id,name')
  ->withCount('likes')
  ->latest();

  if ($viewer !== null) {
  $merchItemsQuery->withExists(['likes as liked' => function ($q) use ($viewer) {
    $q->where('user_id', $viewer->id);
  }]);
  }

  $merchItems = $merchItemsQuery
  ->paginate(12)
  ->withQueryString();
  $editHistories = $band->editHistories()
  ->with('user:id,name,username')
  ->limit(40)
  ->get(['id', 'band_id', 'user_id', 'changes', 'created_at']);

  $canEdit = $viewer !== null && Gate::allows('update', $band);
  $canRequestEdit = $viewer !== null && Gate::allows('createEditRequest', $band);
  $hasPendingEditRequest = $viewer !== null && $canRequestEdit
  ? BandEditRequest::query()
    ->where('band_id', $band->id)
    ->where('user_id', $viewer->id)
    ->where('status', BandEditRequest::STATUS_PENDING)
    ->exists()
  : false;

  return Inertia::render('Bands/Show', [
  'band' => $band,
  'merchItems' => $merchItems,
  'canEdit' => $canEdit,
  'canRequestEdit' => $canRequestEdit,
  'hasPendingEditRequest' => $hasPendingEditRequest,
  'editHistories' => $editHistories,
  'returnTo' => route('bands.index', array_filter([
    'page' => $request->query('page'),
    'letter' => $request->query('letter'),
  ], fn ($value) => filled($value))),
  ]);
  }

  public function edit(Band $band): Response
  {
  $this->authorize('update', $band);
  $band->load(['genres:id', 'links:id,band_id,url,sort_order']);

  return Inertia::render('Bands/Edit', [
  'band' => [
    ...$band->toArray(),
    'genre_ids' => $band->genres->pluck('id')->all(),
    'links' => $band->links->pluck('url')->pad(3, '')->take(3)->values()->all(),
  ],
  'countries' => Country::query()->orderBy('name')->get(['id', 'name']),
  'genres' => Genre::query()->orderBy('name')->get(['id', 'name']),
  ]);
  }

  public function update(UpdateBandRequest $request, Band $band): RedirectResponse
  {
  $this->authorize('update', $band);
  $payload = $request->validated();

  BandUpdater::applyValidatedPayload($band, $payload, $request->user()->id, $request);

  return redirect()->route('bands.show', $band)->with('status', 'band-updated');
  }
}
