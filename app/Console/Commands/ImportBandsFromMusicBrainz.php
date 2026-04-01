<?php

namespace App\Console\Commands;

use App\Models\Band;
use App\Models\Country;
use App\Models\Genre;
use App\Models\User;
use App\Support\BuildsUniqueSlug;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class ImportBandsFromMusicBrainz extends Command
{
  protected const GENRE_BUCKETS = [
    'punk' => ['label' => 'Punk', 'terms' => ['punk rock', 'punk']],
    'hardcore' => ['label' => 'Hardcore', 'terms' => ['hardcore punk', 'hardcore']],
    'emo' => ['label' => 'Emo', 'terms' => ['emo']],
    'pop-punk' => ['label' => 'Pop Punk', 'terms' => ['pop punk']],
    'alternative' => ['label' => 'Alternative', 'terms' => ['alternative rock', 'alternative']],
    'grunge' => ['label' => 'Grunge', 'terms' => ['grunge']],
    'indie-rock' => ['label' => 'Indie', 'terms' => ['indie rock', 'indie']],
    'dream-pop' => ['label' => 'Dream Pop', 'terms' => ['dream pop']],
    'shoegaze' => ['label' => 'Shoegaze', 'terms' => ['shoegaze']],
  ];

  protected $signature = 'bands:import-musicbrainz
    {--limit=5000 : Number of artists to import}
    {--page-size=100 : Number of rows to fetch per request}
    {--countries= : Comma-separated ISO country codes like US,GB,IE,AU}
    {--genres= : Comma-separated genres like punk,hardcore,emo,pop-punk,alternative,grunge,indie-rock,dream-pop,shoegaze}
    {--user-id= : Existing user ID to own imported bands}
    {--sleep-ms=1000 : Milliseconds to wait between requests}
    {--dry-run : Fetch and preview without writing to the database}';

  protected $description = 'Import band data from MusicBrainz';

  /**
   * @var array<string, true>
   */
  protected array $existingComparableNames = [];

  public function handle(): int
  {
    $limit = max(1, (int) $this->option('limit'));
    $pageSize = min(100, max(1, (int) $this->option('page-size')));
    $sleepMs = max(0, (int) $this->option('sleep-ms'));
    $dryRun = (bool) $this->option('dry-run');
    $allowedCountries = $this->parseAllowedCountries();
    $selectedGenres = $this->parseSelectedGenres();
    $owner = $this->resolveOwner($dryRun);
    $buckets = $this->buildBuckets($allowedCountries, $selectedGenres);

    if (! $dryRun && $owner === null) {
      $this->error('インポート用のユーザーを解決できませんでした。');

      return self::FAILURE;
    }

    $created = 0;
    $updated = 0;
    $skipped = 0;
    $processed = 0;
    $seenArtists = [];
    $this->existingComparableNames = Band::query()
      ->pluck('name')
      ->map(fn (string $name) => Band::normalizeComparableName($name))
      ->filter()
      ->mapWithKeys(fn (string $name) => [$name => true])
      ->all();

    $this->info("MusicBrainz から最大 {$limit} 件のバンドを取得します。");

    while ($processed < $limit && $this->hasActiveBuckets($buckets)) {
      foreach ($buckets as &$bucket) {
        if ($processed >= $limit) {
          break;
        }

        if ($bucket['exhausted']) {
          continue;
        }

        $remaining = $limit - $processed;
        $batchSize = min($pageSize, $remaining);
        try {
          $artists = $this->fetchArtists($batchSize, $bucket['offset'], $bucket['query']);
        } catch (RuntimeException $exception) {
          $this->error($exception->getMessage());

          return self::FAILURE;
        }

        if ($artists === []) {
          $bucket['exhausted'] = true;

          continue;
        }

        $bucket['offset'] += count($artists);

        foreach ($artists as $artist) {
          if (($artist['type'] ?? null) !== 'Group') {
            continue;
          }

          if ($allowedCountries !== [] && ! in_array($this->extractCountryCode($artist), $allowedCountries, true)) {
            continue;
          }

          $artistKey = $this->artistDedupKey($artist);

          if ($artistKey === null || isset($seenArtists[$artistKey])) {
            continue;
          }

          if ($this->shouldSkipDuplicateByName($artist)) {
            $skipped++;

            continue;
          }

          $seenArtists[$artistKey] = true;

          if ($dryRun) {
            $processed++;

            continue;
          }

          [$band, $wasCreated] = DB::transaction(fn () => $this->upsertBand($artist, $owner, $bucket['genre']));

          if ($wasCreated) {
            $created++;
          } else {
            $updated++;
          }

          $this->rememberComparableName($band->name);

          $processed++;

          if ($processed >= $limit) {
            break;
          }
        }

        $this->line("進捗: {$processed}/{$limit} 件 ({$bucket['label']})");

        if (! app()->runningUnitTests() && $sleepMs > 0 && $processed < $limit) {
          usleep($sleepMs * 1000);
        }
      }
    }

    if ($dryRun) {
      $this->info("ドライラン完了: {$processed} 件の候補を確認しました。");

      return self::SUCCESS;
    }

    $this->info("完了: 作成 {$created} / 更新 {$updated} / スキップ {$skipped} / 合計 {$processed}");

    return self::SUCCESS;
  }

  protected function fetchArtists(int $limit, int $offset, string $query): array
  {
    $response = Http::withHeaders([
      'User-Agent' => 'my-merch-gallery/0.1 (https://example.com)',
      'Accept' => 'application/json',
    ])->baseUrl('https://musicbrainz.org/ws/2')
      ->retry(3, 1000)
      ->timeout(30)
      ->get('artist', [
        'fmt' => 'json',
        'limit' => $limit,
        'offset' => $offset,
        'query' => $query,
      ]);

    if ($response->failed()) {
      throw new RuntimeException('MusicBrainz API の取得に失敗しました。HTTP '.$response->status());
    }

    return $response->json('artists', []);
  }

  /**
   * @return array{0: Band, 1: bool}
   */
  protected function upsertBand(array $artist, User $owner, ?string $seedGenre = null): array
  {
    $musicBrainzId = trim((string) ($artist['id'] ?? ''));
    $name = trim((string) ($artist['name'] ?? ''));
    $band = $musicBrainzId !== ''
      ? Band::query()->firstOrNew(['musicbrainz_id' => $musicBrainzId])
      : new Band();
    $wasCreated = ! $band->exists;

    if ($wasCreated) {
      $band->created_by = $owner->id;
      $band->slug = BuildsUniqueSlug::for(new Band(), $name);
    }

    if ($musicBrainzId !== '' && $band->musicbrainz_id === null) {
      $band->musicbrainz_id = $musicBrainzId;
    }

    $band->country_id = $this->resolveCountryId($artist);
    $band->name = $name;
    $band->sort_name = Band::normalizeSortName($name);
    $band->formed_year = $this->resolveFormedYear($artist);
    $band->is_active = $this->resolveActiveStatus($artist);
    $band->save();

    $band->genres()->sync($this->resolveGenreIds($artist, $seedGenre));
    return [$band, $wasCreated];
  }

  protected function resolveOwner(bool $dryRun): ?User
  {
    $userId = $this->option('user-id');

    if ($userId !== null) {
      return User::query()->find($userId);
    }

    if ($dryRun) {
      return User::query()->first();
    }

    return User::query()->updateOrCreate(
      ['email' => 'musicbrainz-importer@example.com'],
      [
        'name' => 'MusicBrainz Importer',
        'username' => 'musicbrainz_importer',
        'password' => Hash::make(Str::random(32)),
        'role' => 'admin',
      ],
    );
  }

  protected function resolveCountryId(array $artist): ?int
  {
    $isoCode = $this->extractCountryCode($artist);
    $name = trim((string) data_get($artist, 'area.name', ''));

    if ($isoCode !== '') {
      $country = Country::query()->firstOrCreate(
        ['iso_code' => $isoCode],
        ['name' => $name !== '' ? $name : $isoCode],
      );

      return $country->id;
    }

    if ($name === '') {
      return null;
    }

    return Country::query()->where('name', $name)->value('id');
  }

  protected function extractCountryCode(array $artist): string
  {
    $isoCode = strtoupper((string) ($artist['country'] ?? ''));
    $name = trim((string) data_get($artist, 'area.name', ''));

    $areaAliases = [
      'England' => 'GB',
      'Scotland' => 'GB',
      'Wales' => 'GB',
      'Northern Ireland' => 'GB',
    ];

    if ($isoCode === '' && array_key_exists($name, $areaAliases)) {
      return $areaAliases[$name];
    }

    return $isoCode;
  }

  /**
   * @return array<int, string>
   */
  protected function parseAllowedCountries(): array
  {
    $raw = trim((string) $this->option('countries'));

    if ($raw === '') {
      return [];
    }

    return collect(explode(',', $raw))
      ->map(fn (string $code) => strtoupper(trim($code)))
      ->filter()
      ->unique()
      ->values()
      ->all();
  }

  /**
   * @return array<int, array{key: string, label: string, terms: array<int, string>}>
   */
  protected function parseSelectedGenres(): array
  {
    $raw = trim((string) $this->option('genres'));

    if ($raw === '') {
      return array_map(
        fn (string $key, array $bucket) => ['key' => $key, 'label' => $bucket['label'], 'terms' => $bucket['terms']],
        array_keys(self::GENRE_BUCKETS),
        array_values(self::GENRE_BUCKETS),
      );
    }

    return collect(explode(',', $raw))
      ->map(fn (string $genre) => Str::of($genre)->lower()->replace('_', '-')->replace(' ', '-')->squish()->toString())
      ->filter(fn (string $genre) => array_key_exists($genre, self::GENRE_BUCKETS))
      ->unique()
      ->map(fn (string $genre) => [
        'key' => $genre,
        'label' => self::GENRE_BUCKETS[$genre]['label'],
        'terms' => self::GENRE_BUCKETS[$genre]['terms'],
      ])
      ->values()
      ->all();
  }

  protected function resolveFormedYear(array $artist): ?int
  {
    $begin = (string) data_get($artist, 'life-span.begin', '');

    if (preg_match('/^\d{4}/', $begin, $matches) !== 1) {
      return null;
    }

    return (int) $matches[0];
  }

  protected function resolveActiveStatus(array $artist): bool
  {
    return data_get($artist, 'life-span.ended') !== true;
  }

  /**
   * @return array<int>
   */
  protected function resolveGenreIds(array $artist, ?string $seedGenre = null): array
  {
    $tags = collect($artist['tags'] ?? [])
      ->sortByDesc(fn (array $tag) => (int) ($tag['count'] ?? 0))
      ->pluck('name');

    if ($tags->isEmpty() && $seedGenre !== null) {
      $tags->prepend($seedGenre);
    }

    $tags = $tags
      ->filter()
      ->map(fn (string $name) => $this->normalizeGenreName($name))
      ->filter()
      ->unique()
      ->take(3);

    return $tags->map(function (string $name): int {
      $genre = Genre::query()->firstOrCreate(
        ['slug' => Str::slug($name)],
        ['name' => $name],
      );

      return $genre->id;
    })->values()->all();
  }

  protected function normalizeGenreName(string $name): ?string
  {
    $normalized = Str::of($name)
      ->lower()
      ->replaceMatches('/[^a-z0-9\s-]/', '')
      ->replace('-', ' ')
      ->squish()
      ->toString();

    if ($normalized === '') {
      return null;
    }

    $aliases = [
      'alternative rock' => 'Alternative',
      'alternative' => 'Alternative',
      'grunge' => 'Grunge',
      'pop punk' => 'Pop Punk',
      'indie rock' => 'Indie',
      'indie pop' => 'Indie',
      'punk rock' => 'Punk',
      'hardcore punk' => 'Hardcore',
      'post punk' => 'Post Punk',
      'post rock' => 'Post Rock',
      'dream pop' => 'Dream Pop',
      'hip hop' => 'Hip Hop',
      'shoegaze' => 'Shoegaze',
      'noise rock' => 'Noise',
      'synthpop' => 'Electronic',
    ];

    if (array_key_exists($normalized, $aliases)) {
      return $aliases[$normalized];
    }

    return Str::of($normalized)
      ->title()
      ->replace('Hop', 'Hop')
      ->toString();
  }

  /**
   * @param  array<int, string>  $allowedCountries
   * @param  array<int, array{key: string, label: string, terms: array<int, string>}>  $selectedGenres
   * @return array<int, array{genre: string, label: string, query: string, offset: int, exhausted: bool}>
   */
  protected function buildBuckets(array $allowedCountries, array $selectedGenres): array
  {
    $countries = $allowedCountries === [] ? [null] : $allowedCountries;
    $buckets = [];

    foreach ($selectedGenres as $genre) {
      foreach ($countries as $country) {
        $label = $country === null
          ? $genre['label']
          : $genre['label'].'/'.$country;

        $buckets[] = [
          'genre' => $genre['label'],
          'label' => $label,
          'query' => $this->buildBucketQuery($genre['terms'], $country),
          'offset' => 0,
          'exhausted' => false,
        ];
      }
    }

    return $buckets;
  }

  /**
   * @param  array<int, string>  $genreTerms
   */
  protected function buildBucketQuery(array $genreTerms, ?string $country): string
  {
    $query = 'type:group';

    if ($country !== null) {
      $query .= ' AND country:'.$country;
    }

    $genreQuery = collect($genreTerms)
      ->map(fn (string $term) => str_contains($term, ' ') ? 'tag:"'.$term.'"' : 'tag:'.$term)
      ->implode(' OR ');

    return $query.' AND ('.$genreQuery.')';
  }

  /**
   * @param  array<int, array{genre: string, label: string, query: string, offset: int, exhausted: bool}>  $buckets
   */
  protected function hasActiveBuckets(array $buckets): bool
  {
    return collect($buckets)->contains(fn (array $bucket) => $bucket['exhausted'] === false);
  }

  protected function artistDedupKey(array $artist): ?string
  {
    $id = trim((string) ($artist['id'] ?? ''));

    if ($id !== '') {
      return 'id:'.$id;
    }

    $name = Str::lower(trim((string) ($artist['name'] ?? '')));

    return $name !== '' ? 'name:'.$name : null;
  }

  protected function shouldSkipDuplicateByName(array $artist): bool
  {
    $musicBrainzId = trim((string) ($artist['id'] ?? ''));

    if ($musicBrainzId !== '' && Band::query()->where('musicbrainz_id', $musicBrainzId)->exists()) {
      return false;
    }

    $comparableName = Band::normalizeComparableName((string) ($artist['name'] ?? ''));

    return $comparableName !== '' && isset($this->existingComparableNames[$comparableName]);
  }

  protected function rememberComparableName(string $name): void
  {
    $comparableName = Band::normalizeComparableName($name);

    if ($comparableName !== '') {
      $this->existingComparableNames[$comparableName] = true;
    }
  }
}
