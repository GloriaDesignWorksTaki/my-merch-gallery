<?php

namespace Tests\Feature;

use App\Models\Band;
use App\Models\MerchCategory;
use App\Models\MerchImage;
use App\Models\MerchItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MerchImageUploadTest extends TestCase
{
  use RefreshDatabase;

  public function test_user_can_create_a_merch_item_with_images(): void
  {
    Storage::fake('public');

    $user = User::factory()->create();
    $band = $this->createBand($user);
    $category = $this->createCategory();

    $response = $this
      ->actingAs($user)
      ->post(route('merch-items.store'), [
        'band_id' => $band->id,
        'merch_category_id' => $category->id,
        'name' => 'Tour Tee',
        'description' => 'Front print tee',
        'release_year' => 2018,
        'era_label' => '2018 Tour',
        'is_official' => true,
        'source_type' => 'user_created',
        'images' => [
          UploadedFile::fake()->image('merch-1.jpg'),
          UploadedFile::fake()->image('merch-2.png'),
        ],
      ]);

    $response->assertRedirect();

    $merchItem = MerchItem::query()->firstOrFail();

    $this->assertDatabaseHas('merch_items', [
      'id' => $merchItem->id,
      'band_id' => $band->id,
      'merch_category_id' => $category->id,
      'created_by' => $user->id,
    ]);
    $this->assertCount(2, $merchItem->images);

    foreach ($merchItem->images as $image) {
      $this->assertTrue(Storage::disk('public')->exists($image->image_path));
    }
  }

  public function test_user_can_replace_merch_item_images_on_update(): void
  {
    Storage::fake('public');

    $user = User::factory()->create();
    $band = $this->createBand($user);
    $category = $this->createCategory();
    $merchItem = MerchItem::query()->create([
      'band_id' => $band->id,
      'merch_category_id' => $category->id,
      'created_by' => $user->id,
      'name' => 'Hoodie',
      'slug' => 'hoodie',
      'description' => 'Old image',
      'release_year' => 2020,
      'era_label' => '2020',
      'is_official' => true,
      'source_type' => 'user_created',
    ]);

    $oldPath = UploadedFile::fake()->image('old-merch.jpg')->store('merch-items', 'public');

    MerchImage::query()->create([
      'merch_item_id' => $merchItem->id,
      'image_path' => $oldPath,
      'sort_order' => 1,
    ]);

    $response = $this
      ->actingAs($user)
      ->patch(route('merch-items.update', $merchItem), [
        'band_id' => $band->id,
        'merch_category_id' => $category->id,
        'name' => 'Hoodie',
        'description' => 'New image',
        'release_year' => 2020,
        'era_label' => '2020',
        'is_official' => true,
        'source_type' => 'user_created',
        'images' => [
          UploadedFile::fake()->image('new-merch.webp'),
        ],
      ]);

    $response->assertRedirect(route('merch-items.show', $merchItem));

    $merchItem->refresh();

    $this->assertSame('New image', $merchItem->description);
    $this->assertCount(1, $merchItem->images);
    $this->assertFalse(Storage::disk('public')->exists($oldPath));
    $this->assertTrue(Storage::disk('public')->exists($merchItem->images->first()->image_path));
  }

  protected function createBand(User $user): Band
  {
    return Band::query()->create([
      'created_by' => $user->id,
      'name' => 'The Merch Band',
      'slug' => 'the-merch-band',
      'description' => 'Test band',
      'is_active' => true,
    ]);
  }

  protected function createCategory(): MerchCategory
  {
    return MerchCategory::query()->create([
      'name' => 'T-Shirt',
      'slug' => 't-shirt',
      'sort_order' => 1,
    ]);
  }
}
