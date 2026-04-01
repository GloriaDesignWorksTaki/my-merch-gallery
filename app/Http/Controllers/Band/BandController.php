<?php

namespace App\Http\Controllers\Band;

use App\Http\Controllers\Controller;
use App\Http\Requests\Band\StoreBandRequest;
use App\Http\Requests\Band\UpdateBandRequest;
use App\Models\Band;
use App\Models\Country;
use App\Models\Genre;
use App\Models\Post;
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
      ->select(['id', 'name', 'slug', 'country_id'])
      ->with('country:id,name')
      ->withCount('merchItems');

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
        'slug' => BuildsUniqueSlug::for(new Band(), $payload['name']),
        'sort_name' => Band::normalizeSortName($payload['name']),
        'country_id' => $payload['country_id'] ?? null,
        'description' => $payload['description'] ?? null,
        'formed_year' => $payload['formed_year'] ?? null,
        'is_active' => $payload['is_active'],
      ]);

      $band->genres()->sync($payload['genre_ids'] ?? []);
      $this->syncLinks($band, $payload['links'] ?? []);

      return $band;
    });

    return redirect()->route('bands.show', $band)->with('status', 'band-created');
  }

  public function show(Request $request, Band $band): Response
  {
    $band->load([
      'creator:id,name,username',
      'country:id,name',
      'genres:id,name',
      'links:id,band_id,url,sort_order',
    ]);
    $merchItems = $band->merchItems()
      ->select(['id', 'band_id', 'merch_category_id', 'name', 'slug', 'release_year', 'is_official'])
      ->with('category:id,name')
      ->latest()
      ->paginate(12)
      ->withQueryString();
    $relatedPosts = Post::query()
      ->select(['id', 'user_id', 'band_id', 'merch_item_id', 'body', 'published_at', 'visibility'])
      ->visibleTo(Auth::user())
      ->where('band_id', $band->id)
      ->with(['user:id,name,username', 'coverImage:id,post_id,image_path'])
      ->latest('published_at')
      ->limit(4)
      ->get();

    return Inertia::render('Bands/Show', [
      'band' => $band,
      'merchItems' => $merchItems,
      'canEdit' => Auth::check() && Gate::allows('update', $band),
      'relatedPosts' => $relatedPosts,
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

    DB::transaction(function () use ($band, $payload) {
      $band->update([
        'name' => $payload['name'],
        'slug' => BuildsUniqueSlug::for($band, $payload['name'], $band->id),
        'sort_name' => Band::normalizeSortName($payload['name']),
        'country_id' => $payload['country_id'] ?? null,
        'description' => $payload['description'] ?? null,
        'formed_year' => $payload['formed_year'] ?? null,
        'is_active' => $payload['is_active'],
      ]);

      $band->genres()->sync($payload['genre_ids'] ?? []);
      $this->syncLinks($band, $payload['links'] ?? []);
    });

    return redirect()->route('bands.show', $band)->with('status', 'band-updated');
  }

  protected function syncLinks(Band $band, array $links): void
  {
    $band->links()->delete();

    $rows = collect($links)
      ->filter(fn ($url) => filled($url))
      ->values();

    foreach ($rows as $index => $url) {
      $band->links()->create([
        'url' => $url,
        'sort_order' => $index + 1,
      ]);
    }
  }
}
