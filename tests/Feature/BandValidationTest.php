<?php

namespace Tests\Feature;

use App\Models\Band;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BandValidationTest extends TestCase
{
  use RefreshDatabase;

  public function test_band_store_rejects_duplicate_name(): void
  {
    $user = User::factory()->create();

    Band::query()->create([
      'created_by' => $user->id,
      'name' => 'Jimmy Eat World',
      'slug' => 'jimmy-eat-world',
      'sort_name' => Band::normalizeSortName('Jimmy Eat World'),
      'is_active' => true,
    ]);

    $response = $this
      ->actingAs($user)
      ->post(route('bands.store'), [
        'name' => 'Jimmy Eat World',
        'is_active' => true,
      ]);

    $response
      ->assertSessionHasErrors(['name']);

    $this->assertSame(1, Band::query()->where('name', 'Jimmy Eat World')->count());
  }

  public function test_band_update_rejects_duplicate_name(): void
  {
    $user = User::factory()->create();

    $firstBand = Band::query()->create([
      'created_by' => $user->id,
      'name' => 'The Story So Far',
      'slug' => 'the-story-so-far',
      'sort_name' => Band::normalizeSortName('The Story So Far'),
      'is_active' => true,
    ]);

    $secondBand = Band::query()->create([
      'created_by' => $user->id,
      'name' => 'Neck Deep',
      'slug' => 'neck-deep',
      'sort_name' => Band::normalizeSortName('Neck Deep'),
      'is_active' => true,
    ]);

    $response = $this
      ->actingAs($user)
      ->patch(route('bands.update', $secondBand), [
        'name' => 'The Story So Far',
        'is_active' => true,
      ]);

    $response->assertSessionHasErrors(['name']);
    $this->assertSame('Neck Deep', $secondBand->fresh()->name);
    $this->assertSame('The Story So Far', $firstBand->fresh()->name);
  }
}
