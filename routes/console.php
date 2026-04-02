<?php
/**
 * コンソールルート
 * @description コンソールルートを定義する
 * @author Gloria Design Works
 * @copyright 2026 Gloria Design Works
 * @version 1.00.000
*/
use App\Console\Commands\ImportBandsFromMusicBrainz;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

// コマンドを追加
Artisan::addCommands([
  ImportBandsFromMusicBrainz::class,
]);

// 引用を表示
Artisan::command('inspire', function () {
  $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
