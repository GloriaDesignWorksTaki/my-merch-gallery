<?php

namespace Tests\Feature;

use App\Models\Band;
use App\Models\MerchItem;
use App\Models\User;
use Database\Seeders\MerchCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SitemapTest extends TestCase
{
  use RefreshDatabase;

  public function test_sitemap_xml_is_valid_and_lists_core_routes(): void
  {
    $response = $this->get('/sitemap.xml');
    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
    $content = $response->getContent();
    $this->assertNotFalse($content);
    $this->assertStringContainsString('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', $content);
    $this->assertStringContainsString(route('home', [], true), $content);
    $this->assertStringContainsString(route('bands.index', [], true), $content);
  }

  public function test_sitemap_includes_band_merch_and_public_user_urls(): void
  {
    $this->seed(MerchCategorySeeder::class);
    $user = User::factory()->create();
    $categoryId = DB::table('merch_categories')->where('slug', 'other')->value('id');
    $this->assertNotNull($categoryId);

    $band = Band::query()->create([
      'created_by' => $user->id,
      'name' => 'Test Band',
      'slug' => 'test-band',
      'is_active' => true,
    ]);

    $merch = MerchItem::query()->create([
      'band_id' => $band->id,
      'merch_category_id' => (int) $categoryId,
      'created_by' => $user->id,
      'name' => 'Test Tee',
      'slug' => 'test-tee',
      'is_official' => true,
      'source_type' => 'user_created',
    ]);

    $response = $this->get('/sitemap.xml');
    $response->assertOk();
    $content = $response->getContent();
    $this->assertNotFalse($content);
    $this->assertStringContainsString(route('bands.show', $band, true), $content);
    $this->assertStringContainsString(route('merch-items.show', $merch, true), $content);
    $this->assertStringContainsString(route('users.show', $user, true), $content);
  }

  public function test_robots_txt_points_to_sitemap(): void
  {
    $response = $this->get('/robots.txt');
    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
    $this->assertStringContainsString('Sitemap: '.route('sitemap', [], true), $response->getContent());
  }
}
