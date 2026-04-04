<?php

namespace App\Http\Controllers;

use App\Models\Band;
use App\Models\MerchItem;
use App\Support\Search\FlexibleSearch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $q = trim((string) $request->string('q')->toString());
        $tab = $request->string('tab')->toString();
        $allowedTabs = ['bands', 'merch'];
        if (! in_array($tab, $allowedTabs, true)) {
            $tab = 'bands';
        }

        $perPage = 24;

        $counts = [
            'bands' => 0,
            'merch' => 0,
        ];

        $bands = null;
        $merchItems = null;

        $tokens = FlexibleSearch::tokens($q);

        if ($tokens !== []) {
            $bandsBase = Band::query()
                ->select(['id', 'name', 'slug', 'country_id'])
                ->with('country:id,name')
                ->withCount('merchItems');

            FlexibleSearch::whereAllTokensMatch($bandsBase, $tokens, function (Builder $inner, string $pattern): void {
                $inner->where(function (Builder $sub) use ($pattern): void {
                    FlexibleSearch::whereLowerLike($sub, 'name', $pattern);
                    FlexibleSearch::orWhereLowerLike($sub, 'description', $pattern);
                    $sub->orWhereHas('country', function (Builder $c) use ($pattern): void {
                        FlexibleSearch::whereLowerLike($c, 'name', $pattern);
                    })->orWhereHas('genres', function (Builder $g) use ($pattern): void {
                        FlexibleSearch::whereLowerLike($g, 'name', $pattern);
                    });
                });
            });

            $counts['bands'] = (clone $bandsBase)->count();

            $merchBase = MerchItem::query()
                ->select(['id', 'band_id', 'merch_category_id', 'name', 'slug', 'release_year', 'is_official', 'size_note'])
                ->with([
                    'band:id,name,slug',
                    'category:id,name',
                    'coverImage:id,merch_item_id,image_path,alt_text',
                ]);

            FlexibleSearch::whereAllTokensMatch($merchBase, $tokens, function (Builder $inner, string $pattern): void {
                $inner->where(function (Builder $sub) use ($pattern): void {
                    FlexibleSearch::whereLowerLike($sub, 'name', $pattern);
                    FlexibleSearch::orWhereLowerLike($sub, 'description', $pattern);
                    FlexibleSearch::orWhereLowerLike($sub, 'size_note', $pattern);
                    $sub->orWhereHas('category', function (Builder $c) use ($pattern): void {
                        FlexibleSearch::whereLowerLike($c, 'name', $pattern);
                    })->orWhereHas('band', function (Builder $b) use ($pattern): void {
                        FlexibleSearch::whereLowerLike($b, 'name', $pattern);
                    });
                });
            });

            $counts['merch'] = (clone $merchBase)->count();

            match ($tab) {
                'bands' => $bands = $bandsBase
                    ->orderBy('sort_name')
                    ->orderBy('name')
                    ->paginate($perPage)
                    ->withQueryString(),
                default => $merchItems = $merchBase
                    ->latest()
                    ->paginate($perPage)
                    ->withQueryString(),
            };
        }

        return Inertia::render('Search/Index', [
            'q' => $q,
            'tab' => $tab,
            'counts' => $counts,
            'bands' => $bands,
            'merchItems' => $merchItems,
        ]);
    }
}
