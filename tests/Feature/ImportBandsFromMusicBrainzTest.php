<?php

namespace Tests\Feature;

use App\Models\Band;
use App\Models\Country;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ImportBandsFromMusicBrainzTest extends TestCase
{
  use RefreshDatabase;

  public function test_musicbrainz_import_command_imports_band_data(): void
  {
    Http::fake([
      'https://musicbrainz.org/ws/2/artist*' => Http::response([
        'artists' => [
          [
            'id' => 'artist-1',
            'name' => 'The Example Band',
            'type' => 'Group',
            'country' => 'US',
            'area' => ['name' => 'United States'],
            'life-span' => [
              'begin' => '2004-01-01',
              'ended' => false,
            ],
            'tags' => [
              ['name' => 'alternative rock', 'count' => 10],
              ['name' => 'emo', 'count' => 7],
            ],
          ],
        ],
      ], 200),
    ]);

    Artisan::call('bands:import-musicbrainz', [
      '--limit' => 1,
      '--page-size' => 1,
    ]);

    $band = Band::query()->firstOrFail();

    $this->assertSame('The Example Band', $band->name);
    $this->assertSame('artist-1', $band->musicbrainz_id);
    $this->assertSame(2004, $band->formed_year);
    $this->assertTrue($band->is_active);

    $country = Country::query()->findOrFail($band->country_id);
    $this->assertSame('US', $country->iso_code);

    $genreNames = $band->genres()->orderBy('name')->pluck('name')->all();

    $this->assertSame(['Alternative', 'Emo'], $genreNames);
    $this->assertDatabaseHas('users', [
      'email' => 'musicbrainz-importer@example.com',
      'username' => 'musicbrainz_importer',
    ]);
  }

  public function test_musicbrainz_import_command_can_filter_by_country(): void
  {
    Http::fake([
      'https://musicbrainz.org/ws/2/artist*' => Http::response([
        'artists' => [
          [
            'id' => 'artist-us',
            'name' => 'US Band',
            'type' => 'Group',
            'country' => 'US',
            'area' => ['name' => 'United States'],
            'life-span' => ['begin' => '2001-01-01', 'ended' => false],
            'tags' => [],
          ],
          [
            'id' => 'artist-jp',
            'name' => 'JP Band',
            'type' => 'Group',
            'country' => 'JP',
            'area' => ['name' => 'Japan'],
            'life-span' => ['begin' => '2002-01-01', 'ended' => false],
            'tags' => [],
          ],
        ],
      ], 200),
    ]);

    Artisan::call('bands:import-musicbrainz', [
      '--limit' => 1,
      '--page-size' => 2,
      '--countries' => 'US,GB,IE,AU',
    ]);

    $this->assertDatabaseHas('bands', ['name' => 'US Band']);
    $this->assertDatabaseMissing('bands', ['name' => 'JP Band']);
  }

  public function test_musicbrainz_import_command_round_robins_across_genre_buckets(): void
  {
    Http::fake(function (\Illuminate\Http\Client\Request $request) {
      $query = (string) $request->data()['query'];

      if (str_contains($query, 'tag:emo')) {
        return Http::response([
          'artists' => [
            [
              'id' => 'artist-emo',
              'name' => 'Emo Example',
              'type' => 'Group',
              'country' => 'US',
              'area' => ['name' => 'United States'],
              'life-span' => ['begin' => '2003-01-01', 'ended' => false],
              'tags' => [],
            ],
          ],
        ], 200);
      }

      return Http::response([
        'artists' => [
          [
            'id' => 'artist-punk',
            'name' => 'Punk Example',
            'type' => 'Group',
            'country' => 'US',
            'area' => ['name' => 'United States'],
            'life-span' => ['begin' => '2001-01-01', 'ended' => false],
            'tags' => [],
          ],
        ],
      ], 200);
    });

    Artisan::call('bands:import-musicbrainz', [
      '--limit' => 2,
      '--page-size' => 1,
      '--countries' => 'US',
      '--genres' => 'punk,emo',
    ]);

    $this->assertDatabaseHas('bands', ['name' => 'Punk Example']);
    $this->assertDatabaseHas('bands', ['name' => 'Emo Example']);

    $punkGenres = Band::query()->where('name', 'Punk Example')->firstOrFail()->genres()->pluck('name')->all();
    $emoGenres = Band::query()->where('name', 'Emo Example')->firstOrFail()->genres()->pluck('name')->all();

    $this->assertContains('Punk', $punkGenres);
    $this->assertContains('Emo', $emoGenres);
  }

  public function test_musicbrainz_import_command_skips_duplicate_band_name(): void
  {
    $user = User::factory()->create();

    Band::query()->create([
      'created_by' => $user->id,
      'name' => 'American Football',
      'slug' => 'american-football-manual',
      'description' => 'Manual entry',
      'is_active' => true,
    ]);

    Http::fake(function (\Illuminate\Http\Client\Request $request) {
      $offset = (int) ($request->data()['offset'] ?? 0);

      if ($offset > 0) {
        return Http::response(['artists' => []], 200);
      }

      return Http::response([
        'artists' => [
          [
            'id' => 'mb-american-football',
            'name' => 'American Football',
            'type' => 'Group',
            'country' => 'US',
            'area' => ['name' => 'United States'],
            'life-span' => ['begin' => '1997-01-01', 'ended' => false],
            'tags' => [
              ['name' => 'emo', 'count' => 5],
            ],
          ],
        ],
      ], 200);
    });

    Artisan::call('bands:import-musicbrainz', [
      '--limit' => 1,
      '--page-size' => 1,
      '--genres' => 'emo',
      '--countries' => 'US',
    ]);

    $this->assertDatabaseHas('bands', [
      'name' => 'American Football',
      'slug' => 'american-football-manual',
      'musicbrainz_id' => null,
    ]);
    $this->assertDatabaseMissing('bands', [
      'musicbrainz_id' => 'mb-american-football',
    ]);
    $this->assertSame(1, Band::query()->where('name', 'American Football')->count());
  }
}
