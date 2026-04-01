<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MerchCategorySeeder extends Seeder
{
  public function run(): void
  {
    $rows = [
      ['name' => 'T-Shirt', 'slug' => 't-shirt', 'sort_order' => 10],
      ['name' => 'Hoodie', 'slug' => 'hoodie', 'sort_order' => 20],
      ['name' => 'Sweatshirt', 'slug' => 'sweatshirt', 'sort_order' => 30],
      ['name' => 'Cap', 'slug' => 'cap', 'sort_order' => 40],
      ['name' => 'Poster', 'slug' => 'poster', 'sort_order' => 50],
      ['name' => 'Sticker', 'slug' => 'sticker', 'sort_order' => 60],
      ['name' => 'Other', 'slug' => 'other', 'sort_order' => 999],
    ];

    foreach ($rows as $row) {
      DB::table('merch_categories')->updateOrInsert(
        ['slug' => $row['slug']],
        [...$row, 'created_at' => now(), 'updated_at' => now()],
      );
    }
  }
}
