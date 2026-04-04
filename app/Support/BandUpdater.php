<?php

namespace App\Support;

use App\Models\Band;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class BandUpdater
{
    /**
     * @param  array<string, mixed>  $payload
     *                                         UpdateBandRequest::validated() 互換（name, country_id, genre_ids, links, description, formed_year, is_active）
     */
    public static function applyValidatedPayload(Band $band, array $payload, int $historyUserId, ?Request $request = null): void
    {
        $before = [
            'attributes' => $band->only(['name', 'slug', 'country_id', 'description', 'formed_year', 'is_active', 'image_path']),
            'genre_ids' => $band->genres()->pluck('genres.id')->sort()->values()->all(),
            'links' => $band->links()->orderBy('sort_order')->pluck('url')->values()->all(),
        ];

        DB::transaction(function () use ($band, $payload, $request) {
            $band->update([
                'name' => $payload['name'],
                'slug' => BuildsUniqueSlug::for($band, $payload['name'], $band->id),
                'sort_name' => Band::normalizeSortName($payload['name']),
                'country_id' => $payload['country_id'] ?? null,
                'description' => $payload['description'] ?? null,
                'formed_year' => $payload['formed_year'] ?? null,
                'is_active' => $payload['is_active'],
            ]);

            $band->genres()->sync($payload['genre_ids'] ?? []);
            self::syncLinks($band, $payload['links'] ?? []);

            if ($request !== null) {
                self::syncBandImage($band, $request);
            }
        });

        BandEditHistoryRecorder::recordIfChanged($band, $historyUserId, $before);
    }

    public static function syncBandImage(Band $band, Request $request): void
    {
        if ($request->boolean('remove_image')) {
            if ($band->image_path !== null) {
                Storage::disk('public')->delete($band->image_path);
            }
            $band->forceFill(['image_path' => null])->save();

            return;
        }

        if ($request->hasFile('image')) {
            if ($band->image_path !== null) {
                Storage::disk('public')->delete($band->image_path);
            }
            $path = $request->file('image')->store('bands', 'public');
            $band->forceFill(['image_path' => $path])->save();
        }
    }

    /**
     * @param  array<int, mixed>  $links
     */
    public static function syncLinks(Band $band, array $links): void
    {
        $band->links()->delete();

        $rows = collect($links)
            ->filter(fn ($url) => filled($url))
            ->values();

        foreach ($rows as $index => $url) {
            $band->links()->create([
                'url' => $url,
                'sort_order' => $index + 1,
            ]);
        }
    }
}
