<?php

use App\Console\Commands\ImportBandsFromMusicBrainz;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::addCommands([
  ImportBandsFromMusicBrainz::class,
]);

Artisan::command('inspire', function () {
  $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
