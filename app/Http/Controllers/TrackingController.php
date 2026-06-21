<?php

namespace App\Http\Controllers;

use App\Enums\TrackingStatus;
use App\Enums\TrackingType;
use App\Jobs\QueryRoyalMailTracking;
use App\Models\Tracking;
use App\Models\TrackingBatch;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Inertia\Inertia;
use Inertia\Response;

class TrackingController extends Controller
{
    /**
     * Display a listing of the tracking numbers for the authenticated user's account.
     */
    public function index(Request $request): Response
    {
        $user = Auth::user();

        $batches = $user->account->trackingBatches()
            ->with('tracking')
            ->where('created_at', '>=', now()->startOfDay())
            ->orderBy('tracking_batch.created_at', 'desc')
            ->get();

        return Inertia::render('Tracking/Index', [
            'batches' => $batches,
            'statuses' => TrackingStatus::labels(), 
        ]);
    }

    /**
     * Display the full tracking history for the authenticated user's account.
     */
    public function history(Request $request): Response
    {
        $user = Auth::user();

        $batches = $user->account->trackingBatches()
            ->with('tracking')
            ->orderBy('tracking_batch.created_at', 'desc')
            ->get();

        return Inertia::render('Tracking/History', [
            'batches' => $batches,
            'statuses' => TrackingStatus::labels(),
        ]);
    }

    /**
     * Return the tracking batches for the user's account within a date range.
     */
    public function all(Request $request): JsonResponse
    {
        $user = Auth::user();
        $format = $request->format;
        $start = $request->start;
        $end = $request->end;

        $batches = $user->account->trackingBatches()
            ->with('tracking')
            ->where('created_at', '>=', $start)
            ->where('created_at', '<=', $end)
            ->orderBy('tracking_batch.created_at', 'desc')
            ->get();

        return response()->json($batches);
    }

    /**
     * Store a newly created tracking number in the user's account.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'number' => 'required|string',
        ]);

        $numbers = array_values(array_filter(array_map('trim', explode("\n", $validated['number']))));

        $user = Auth::user();

        $trackingBatch = $user->account->trackingBatches()->create();

        $alreadyExisting = [];
        $depleted = false;

        foreach ($numbers as $number) {
            if ($user->packages_remaining > 0) {
                $existing = $user->account->tracking()->where('number', $number)->first();

                if (is_null($existing)) {
                    $user->account->tracking()->create([
                        'number' => $number,
                        'type' => TrackingType::RoyalMail->value,
                        'status' => TrackingStatus::Created->value,
                        'tracking_batch_id' => $trackingBatch->id,
                    ]);

                    $user->packages_remaining -= 1;
                    $user->save();
                } else {
                    $alreadyExisting[] = $existing;
                }
            } else {
                $depleted = true;
                break;
            }
        }

        $trackings = $user->account->tracking()->orderBy('created_at', 'desc')->get();

        $queued = $trackings->where('status', TrackingStatus::Created->value);

        $jobs = [];
        foreach ($queued as $job) {
            $jobs[] = (new QueryRoyalMailTracking($job))->delay(now()->addSeconds(10));
        }

        if (! empty($jobs)) {
            Bus::batch($jobs)->name('Query')->onQueue('Query')->dispatch();
        }

        if (count($alreadyExisting) === count($numbers)) {
            $flash = ['error' => 'No tracking created, tracking numbers already exist in our system'];
        } elseif ($depleted) {
            $flash = [
                'error' => 'Tracking numbers not added as the number of available tracking numbers for this month has been depleted',
            ];
        } elseif (count($alreadyExisting) > 0) {
            $flash = [
                'success' => 'Successfully added tracking numbers',
                'error' => 'Some tracking numbers were not added as they already exist in our system',
            ];
        } else {
            $flash = ['success' => 'Successfully added tracking numbers'];
        }

        $batches = $user->account->trackingBatches()
            ->with('tracking')
            ->where('created_at', '>=', now()->startOfDay())
            ->orderBy('tracking_batch.created_at', 'desc')
            ->get();

        return response()->json([
            'flash' => $flash,
            'tracking' => $batches,
        ]);
    }

    /**
     * Remove the specified tracking number from the user's account.
     */
    public function destroy(int $id, bool $redirect = true): ?RedirectResponse
    {
        $tracking = Tracking::findOrFail($id);

        // Ensure the tracking number belongs to the authenticated user's account.
        if (Auth::user()->account->id !== $tracking->account_id) {
            return $redirect
                ? redirect()->route('tracking.index')->with('error', 'Unauthorized action.')
                : null;
        }

        if ($tracking->trackingBatch->tracking()->count() === 1) {
            $batch = $tracking->trackingBatch;

            $tracking->delete();
            $batch->delete();
        } else {
            $tracking->delete();
        }

        return $redirect
            ? redirect()->route('tracking.index')->with('success', 'Tracking number deleted successfully.')
            : null;
    }

    /**
     * Remove multiple tracking numbers from the user's account.
     */
    public function bulkdestroy(Request $request): JsonResponse
    {
        $ids = $request->input('ids');

        foreach ($ids as $id) {
            $this->destroy($id, false);
        }

        return response()->json(['status' => 'success']);
    }
}