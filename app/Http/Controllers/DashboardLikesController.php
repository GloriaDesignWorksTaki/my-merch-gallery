<?php

namespace App\Http\Controllers;

use App\Models\BandLike;
use App\Models\MerchItemLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardLikesController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $tab = $request->string('tab')->toString();
        $allowedTabs = ['bands', 'merch'];
        if (! in_array($tab, $allowedTabs, true)) {
            $tab = 'bands';
        }

        $user = $request->user();

        $countRow = DB::selectOne(
            'select (select count(*) from band_likes where user_id = ?) as bands, (select count(*) from merch_item_likes where user_id = ?) as merch',
            [$user->id, $user->id]
        );
        $counts = [
            'bands' => (int) ($countRow->bands ?? 0),
            'merch' => (int) ($countRow->merch ?? 0),
        ];

        $bands = null;
        $merchItems = null;

        if ($tab === 'bands') {
            $bands = BandLike::query()
                ->where('user_id', $user->id)
                ->with(['band:id,name,slug'])
                ->latest('created_at')
                ->paginate(24)
                ->withQueryString();
        } else {
            $merchItems = MerchItemLike::query()
                ->where('user_id', $user->id)
                ->with([
                    'merchItem:id,name,slug,band_id',
                    'merchItem.band:id,name,slug',
                    'merchItem.coverImage:id,merch_item_id,image_path,alt_text',
                ])
                ->latest('created_at')
                ->paginate(24)
                ->withQueryString();
        }

        return Inertia::render('Dashboard/Likes', [
            'tab' => $tab,
            'counts' => $counts,
            'bands' => $bands,
            'merchItems' => $merchItems,
        ]);
    }
}
