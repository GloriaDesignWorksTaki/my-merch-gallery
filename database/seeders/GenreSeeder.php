<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GenreSeeder extends Seeder
{
  public function run(): void
  {
    $genres = [
      'Alternative',
      'Indie',
      'Rock',
      'Punk',
      'Hardcore',
      'Metal',
      'Emo',
      'Shoegaze',
      'Pop',
      'Hip Hop',
      'Electronic',
      'Jazz',
      'Folk',
      'Experimental',
      'Post Rock',
      'Post Punk',
      'Dream Pop',
      'Noise',
    ];

    foreach ($genres as $name) {
      DB::table('genres')->updateOrInsert(
        ['slug' => Str::slug($name)],
        [
          'name' => $name,
          'slug' => Str::slug($name),
          'updated_at' => now(),
          'created_at' => now(),
        ],
      );
    }
  }
}
