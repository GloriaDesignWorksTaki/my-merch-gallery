<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BandEditRequest;
use App\Support\BandUpdater;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class BandEditRequestAdminController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', BandEditRequest::class);

        $status = $request->string('status')->toString();
        if (! in_array($status, ['pending', 'approved', 'rejected', 'all'], true)) {
            $status = 'pending';
        }

        $query = BandEditRequest::query()
            ->with([
                'band:id,name,slug',
                'user:id,name,username',
                'reviewer:id,name,username',
            ])
            ->latest();

        if ($status !== 'all') {
            $query->where('status', $status === 'pending' ? BandEditRequest::STATUS_PENDING : $status);
        }

        return Inertia::render('Admin/BandEditRequests', [
            'requests' => $query->paginate(20)->withQueryString(),
            'filters' => ['status' => $status],
        ]);
    }

    public function approve(BandEditRequest $bandEditRequest): RedirectResponse
    {
        $this->authorize('review', $bandEditRequest);

        $reviewer = request()->user();
        $band = $bandEditRequest->band;
        $payload = $bandEditRequest->payload;

        DB::transaction(function () use ($bandEditRequest, $band, $payload, $reviewer) {
            BandUpdater::applyValidatedPayload($band, $payload, $reviewer->id);

            $bandEditRequest->update([
                'status' => BandEditRequest::STATUS_APPROVED,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'reviewer_note' => null,
            ]);
        });

        return redirect()->route('admin.band-edit-requests.index', ['status' => 'pending'])
            ->with('status', 'admin-band-request-approved');
    }

    public function reject(Request $request, BandEditRequest $bandEditRequest): RedirectResponse
    {
        $this->authorize('review', $bandEditRequest);

        $validated = $request->validate([
            'reviewer_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $bandEditRequest->update([
            'status' => BandEditRequest::STATUS_REJECTED,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'reviewer_note' => $validated['reviewer_note'] ?? null,
        ]);

        return redirect()->route('admin.band-edit-requests.index', ['status' => 'pending'])
            ->with('status', 'admin-band-request-rejected');
    }
}
