<?php

namespace Tests\Feature;

use App\Models\Band;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PostVisibilityTest extends TestCase
{
  use RefreshDatabase;

  public function test_posts_index_only_shows_public_posts(): void
  {
    $user = User::factory()->create();
    $band = $this->createBand($user);

    Post::query()->create([
      'user_id' => $user->id,
      'band_id' => $band->id,
      'body' => '公開投稿',
      'visibility' => 'public',
      'published_at' => now(),
    ]);

    Post::query()->create([
      'user_id' => $user->id,
      'band_id' => $band->id,
      'body' => '限定投稿',
      'visibility' => 'unlisted',
      'published_at' => now(),
    ]);

    Post::query()->create([
      'user_id' => $user->id,
      'band_id' => $band->id,
      'body' => '非公開投稿',
      'visibility' => 'private',
      'published_at' => now(),
    ]);

    $response = $this->get(route('posts.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
      ->component('Posts/Index')
      ->has('posts.data', 1)
      ->where('posts.data.0.body', '公開投稿'));
  }

  public function test_private_post_is_hidden_from_other_users(): void
  {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $band = $this->createBand($owner);
    $post = Post::query()->create([
      'user_id' => $owner->id,
      'band_id' => $band->id,
      'body' => '見えてはいけない投稿',
      'visibility' => 'private',
      'published_at' => now(),
    ]);

    $this->actingAs($otherUser)
      ->get(route('posts.show', $post))
      ->assertForbidden();

    $this->actingAs($owner)
      ->get(route('posts.show', $post))
      ->assertOk();
  }

  public function test_public_profile_counts_only_public_posts(): void
  {
    $user = User::factory()->create([
      'username' => 'public_profile_user',
    ]);
    $band = $this->createBand($user);

    Post::query()->create([
      'user_id' => $user->id,
      'band_id' => $band->id,
      'body' => '公開投稿',
      'visibility' => 'public',
      'published_at' => now(),
    ]);

    Post::query()->create([
      'user_id' => $user->id,
      'band_id' => $band->id,
      'body' => '非公開投稿',
      'visibility' => 'private',
      'published_at' => now(),
    ]);

    $response = $this->get(route('users.show', $user));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
      ->component('Users/Show')
      ->where('profileUser.posts_count', 1));
  }

  protected function createBand(User $user): Band
  {
    return Band::query()->create([
      'created_by' => $user->id,
      'name' => 'Visibility Band',
      'slug' => 'visibility-band',
      'description' => 'Test band',
      'is_active' => true,
    ]);
  }
}
