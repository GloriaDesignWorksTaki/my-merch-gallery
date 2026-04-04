<?php

namespace App\Http\Controllers\Band;

use App\Http\Controllers\Controller;
use App\Http\Requests\Band\StoreBandEditRequestRequest;
use App\Models\Band;
use App\Models\BandEditRequest;
use App\Models\Country;
use App\Models\Genre;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class BandEditRequestController extends Controller
{
    public function create(Band $band): Response
    {
        $this->authorize('createEditRequest', $band);

        $band->load(['genres:id', 'links:id,band_id,url,sort_order']);

        return Inertia::render('Bands/RequestEdit', [
            'band' => [
                ...$band->toArray(),
                'genre_ids' => $band->genres->pluck('id')->all(),
                'links' => $band->links->pluck('url')->pad(3, '')->take(3)->values()->all(),
            ],
            'countries' => Country::query()->orderBy('name')->get(['id', 'name']),
            'genres' => Genre::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreBandEditRequestRequest $request, Band $band): RedirectResponse
    {
        $exists = BandEditRequest::query()
            ->where('band_id', $band->id)
            ->where('user_id', $request->user()->id)
            ->where('status', BandEditRequest::STATUS_PENDING)
            ->exists();

        if ($exists) {
            return redirect()
                ->route('bands.show', $band)
                ->with('status', 'band-edit-request-duplicate');
        }

        BandEditRequest::create([
            'band_id' => $band->id,
            'user_id' => $request->user()->id,
            'payload' => $request->validated(),
            'status' => BandEditRequest::STATUS_PENDING,
        ]);

        return redirect()->route('bands.show', $band)->with('status', 'band-edit-request-sent');
    }
}
