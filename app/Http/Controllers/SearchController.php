<?php

namespace App\Http\Controllers;

use App\Models\Band;
use App\Models\MerchItem;
use App\Models\Post;
use App\Support\Search\FlexibleSearch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $q = trim((string) $request->string('q')->toString());
        $tab = $request->string('tab')->toString();
        $allowedTabs = ['bands', 'merch', 'posts'];
        if (! in_array($tab, $allowedTabs, true)) {
            $tab = 'bands';
        }

        $perPage = 24;

        $counts = [
            'bands' => 0,
            'merch' => 0,
            'posts' => 0,
        ];

        $bands = null;
        $merchItems = null;
        $posts = null;

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
                ->select(['id', 'band_id', 'merch_category_id', 'name', 'slug', 'release_year', 'is_official'])
                ->with([
                    'band:id,name,slug',
                    'category:id,name',
                    'coverImage:id,merch_item_id,image_path,alt_text',
                ]);

            FlexibleSearch::whereAllTokensMatch($merchBase, $tokens, function (Builder $inner, string $pattern): void {
                $inner->where(function (Builder $sub) use ($pattern): void {
                    FlexibleSearch::whereLowerLike($sub, 'name', $pattern);
                    FlexibleSearch::orWhereLowerLike($sub, 'description', $pattern);
                    FlexibleSearch::orWhereLowerLike($sub, 'era_label', $pattern);
                    $sub->orWhereHas('category', function (Builder $c) use ($pattern): void {
                        FlexibleSearch::whereLowerLike($c, 'name', $pattern);
                    })->orWhereHas('band', function (Builder $b) use ($pattern): void {
                        FlexibleSearch::whereLowerLike($b, 'name', $pattern);
                    });
                });
            });

            $counts['merch'] = (clone $merchBase)->count();

            $postsBase = Post::query()
                ->select(['id', 'user_id', 'band_id', 'merch_item_id', 'body', 'visibility', 'published_at'])
                ->visibleTo(Auth::user())
                ->with([
                    'user:id,name,username',
                    'band:id,name,slug',
                    'merchItem:id,name,slug',
                    'coverImage:id,post_id,image_path',
                ]);

            FlexibleSearch::whereAllTokensMatch($postsBase, $tokens, function (Builder $inner, string $pattern): void {
                $inner->where(function (Builder $sub) use ($pattern): void {
                    FlexibleSearch::whereLowerLike($sub, 'body', $pattern);
                    $sub->orWhereHas('user', function (Builder $u) use ($pattern): void {
                        FlexibleSearch::whereLowerLike($u, 'username', $pattern);
                        FlexibleSearch::orWhereLowerLike($u, 'name', $pattern);
                    })->orWhereHas('band', function (Builder $b) use ($pattern): void {
                        FlexibleSearch::whereLowerLike($b, 'name', $pattern);
                    })->orWhereHas('merchItem', function (Builder $m) use ($pattern): void {
                        FlexibleSearch::whereLowerLike($m, 'name', $pattern);
                    });
                });
            });

            $counts['posts'] = (clone $postsBase)->count();

            match ($tab) {
                'bands' => $bands = $bandsBase
                    ->orderBy('sort_name')
                    ->orderBy('name')
                    ->paginate($perPage)
                    ->withQueryString(),
                'merch' => $merchItems = $merchBase
                    ->latest()
                    ->paginate($perPage)
                    ->withQueryString(),
                default => $posts = $postsBase
                    ->latest('published_at')
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
            'posts' => $posts,
        ]);
    }
}
