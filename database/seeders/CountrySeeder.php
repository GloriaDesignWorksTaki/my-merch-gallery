<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
  public function run(): void
  {
    $rows = [
      ['name' => 'Japan', 'iso_code' => 'JP'],
      ['name' => 'United States', 'iso_code' => 'US'],
      ['name' => 'United Kingdom', 'iso_code' => 'GB'],
      ['name' => 'Canada', 'iso_code' => 'CA'],
      ['name' => 'Australia', 'iso_code' => 'AU'],
      ['name' => 'Germany', 'iso_code' => 'DE'],
      ['name' => 'France', 'iso_code' => 'FR'],
      ['name' => 'South Korea', 'iso_code' => 'KR'],
      ['name' => 'China', 'iso_code' => 'CN'],
      ['name' => 'Taiwan', 'iso_code' => 'TW'],
      ['name' => 'Brazil', 'iso_code' => 'BR'],
      ['name' => 'Mexico', 'iso_code' => 'MX'],
      ['name' => 'Sweden', 'iso_code' => 'SE'],
      ['name' => 'Norway', 'iso_code' => 'NO'],
      ['name' => 'Finland', 'iso_code' => 'FI'],
      ['name' => 'Netherlands', 'iso_code' => 'NL'],
      ['name' => 'Spain', 'iso_code' => 'ES'],
      ['name' => 'Italy', 'iso_code' => 'IT'],
      ['name' => 'Indonesia', 'iso_code' => 'ID'],
      ['name' => 'Philippines', 'iso_code' => 'PH'],
    ];

    foreach ($rows as $row) {
      DB::table('countries')->updateOrInsert(
        ['iso_code' => $row['iso_code']],
        [...$row, 'updated_at' => now(), 'created_at' => now()],
      );
    }
  }
}
