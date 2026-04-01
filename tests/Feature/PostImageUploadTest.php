<?php

namespace Tests\Feature;

use App\Models\Band;
use App\Models\Post;
use App\Models\PostImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostImageUploadTest extends TestCase
{
  use RefreshDatabase;

  public function test_user_can_create_a_post_with_images(): void
  {
    Storage::fake('public');

    $user = User::factory()->create();
    $band = $this->createBand($user);

    $response = $this
      ->actingAs($user)
      ->post(route('posts.store'), [
        'band_id' => $band->id,
        'merch_item_id' => null,
        'body' => '画像つきの投稿です。',
        'visibility' => 'public',
        'images' => [
          UploadedFile::fake()->image('post-1.jpg'),
          UploadedFile::fake()->image('post-2.png'),
        ],
      ]);

    $response->assertRedirect();

    $post = Post::query()->firstOrFail();

    $this->assertDatabaseHas('posts', [
      'id' => $post->id,
      'user_id' => $user->id,
      'band_id' => $band->id,
    ]);
    $this->assertCount(2, $post->images);

    foreach ($post->images as $image) {
      $this->assertTrue(Storage::disk('public')->exists($image->image_path));
    }
  }

  public function test_user_can_replace_post_images_on_update(): void
  {
    Storage::fake('public');

    $user = User::factory()->create();
    $band = $this->createBand($user);
    $post = Post::query()->create([
      'user_id' => $user->id,
      'band_id' => $band->id,
      'body' => '既存画像あり',
      'visibility' => 'public',
      'published_at' => now(),
    ]);

    $oldPath = UploadedFile::fake()->image('old.jpg')->store('posts', 'public');

    PostImage::query()->create([
      'post_id' => $post->id,
      'image_path' => $oldPath,
      'sort_order' => 1,
    ]);

    $response = $this
      ->actingAs($user)
      ->patch(route('posts.update', $post), [
        'band_id' => $band->id,
        'merch_item_id' => null,
        'body' => '画像を差し替えました。',
        'visibility' => 'public',
        'images' => [
          UploadedFile::fake()->image('new-1.jpg'),
        ],
      ]);

    $response->assertRedirect(route('posts.show', $post));

    $post->refresh();

    $this->assertSame('画像を差し替えました。', $post->body);
    $this->assertCount(1, $post->images);
    $this->assertFalse(Storage::disk('public')->exists($oldPath));
    $this->assertTrue(Storage::disk('public')->exists($post->images->first()->image_path));
  }

  protected function createBand(User $user): Band
  {
    return Band::query()->create([
      'created_by' => $user->id,
      'name' => 'The Test Band',
      'slug' => 'the-test-band',
      'description' => 'Test band',
      'is_active' => true,
    ]);
  }
}
