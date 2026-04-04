<?php

namespace App\Http\Controllers\Band;

use App\Http\Controllers\Controller;
use App\Models\Band;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BandLikeController extends Controller
{
    public function toggle(Request $request, Band $band): RedirectResponse
    {
        $this->authorize('like', $band);

        $userId = $request->user()->id;

        DB::transaction(function () use ($band, $userId) {
            $like = $band->likes()->where('user_id', $userId)->first();

            if ($like !== null) {
                $like->delete();
            } else {
                $band->likes()->create(['user_id' => $userId]);
            }
        });

        return back();
    }
}
