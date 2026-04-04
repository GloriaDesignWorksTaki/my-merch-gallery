<?php

namespace App\Support;

use App\Models\Band;
use App\Models\BandEditHistory;

class BandEditHistoryRecorder
{
    /**
     * 更新後のバンドと更新前スナップショットを比較し、差分があれば履歴を保存する。
     *
     * @param  array{attributes: array<string, mixed>, genre_ids: array<int, int>, links: array<int, string>}  $before
     */
    public static function recordIfChanged(Band $band, int $userId, array $before): void
    {
        $band->refresh();
        $band->load('genres:id');

        $changes = [];

        foreach (['name', 'slug', 'country_id', 'description', 'formed_year', 'is_active', 'image_path'] as $key) {
            $old = $before['attributes'][$key] ?? null;
            $new = $band->getAttribute($key);
            if ($old != $new) {
                $changes[$key] = [$old, $new];
            }
        }

        $afterGenreIds = $band->genres()->pluck('genres.id')->sort()->values()->all();
        if ($before['genre_ids'] !== $afterGenreIds) {
            $changes['genre_ids'] = [$before['genre_ids'], $afterGenreIds];
        }

        $afterLinks = $band->links()->orderBy('sort_order')->pluck('url')->values()->all();
        if ($before['links'] !== $afterLinks) {
            $changes['links'] = [$before['links'], $afterLinks];
        }

        if ($changes === []) {
            return;
        }

        BandEditHistory::create([
            'band_id' => $band->id,
            'user_id' => $userId,
            'changes' => $changes,
        ]);
    }
}
