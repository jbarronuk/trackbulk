<?php

namespace App\Http\Controllers;

use App\Enums\TrackingStatus;
use App\Enums\TrackingType;
use App\Jobs\Query;
use App\Models\Tracking;
use App\Models\TrackingBatch;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Redirect;
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
            ->where('created_at', '>', Carbon::now()->startOfDay()->format('Y-m-d H:i:s'))
            ->orderBy('tracking_batch.created_at', 'desc')
            ->get()
            ->map(function ($batch) {
                $batch->formatted_created_at = $batch->created_at->format('H:i');

                return $batch;
            });

        return Inertia::render('Tracking/Index', [
            'batches' => $batches,
            'statuses' => TrackingStatus::all(),
        ]);
    }

    public function history(Request $request)
    {
        $user = Auth::user();

        $batches = $user->account->trackingBatches()
            ->with('tracking')
            ->orderBy('tracking_batch.created_at', 'desc')
            ->get()
            ->map(function ($batch) {
                $batch->formatted_created_at = $batch->created_at->format('d/m/Y H:i');

                return $batch;
            });

        return Inertia::render('Tracking/History', [
            'batches' => $batches,
            'statuses' => TrackingStatus::all(),
        ]);
    }

    public function all(Request $request)
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
            ->get()
            ->map(function ($batch) use ($format) {
                if ($format === 'time') {
                    $batch->formatted_created_at = $batch->created_at->format('H:i');
                } else {
                    $batch->formatted_created_at = $batch->created_at->format('d/m/Y H:i');
                }

                return $batch;
            });

        // $trackings = $user->account->trackingBatches()->orderBy('created_at', 'desc')->get();
        return $batches;
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
        if (count($numbers) > 0) {

            $trackingBatch = TrackingBatch::create(['account_id' => $user->account_id]);

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
                        $user->packages_remaining = $user->packages_remaining - 1;
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

            $queued = $trackings->where('status', TrackingStatus::Created);
            $jobs = [];
            foreach ($queued as $job) {
                $jobs[] = (new Query($job))->delay(now()->addSeconds(10));
            }

            $batch = Bus::batch($jobs)->name('Query')->onQueue('Query')->dispatch();

            if (count($alreadyExisting) === count($numbers)) {
                $flash = ['error' => 'No tracking created, tracking numbers already existing in our system'];
            } elseif ($depleted) {
                $flash = [
                    'error' => 'Tracking numbers not added as the number of available tracking numbers for this month has been depleted',
                ];
            } elseif (count($alreadyExisting) > 0) {
                $flash = [
                    'success' => 'Successfully added tracking numbers',
                    'error' => 'Some tracking number not added as they already existing in our system',
                ];
            } else {
                $flash = ['success' => 'Successfull added tracking numbers'];
            }
        } else {
            $flash = [
                'error' => 'No valid tracking numbers found',
            ];
        }
        $batches = $user->account->trackingBatches()
            ->with('tracking')
            ->where('created_at', '>=', Carbon::now()->startOfDay())
            ->orderBy('tracking_batch.created_at', 'desc')
            ->get()
            ->map(function ($batch) {
                $batch->formatted_created_at = $batch->created_at->format('H:i');

                return $batch;
            });

        // [
        //     'success' => 'Tracking created successfully.',
        //     'error' => 'Tracking created successfully.',
        // ],

        return response()->json([
            'flash' => $flash,
            'tracking' => $batches,
        ]);

        // // Return updated data
        // return Inertia::render('Tracking/Index', [
        //     'tracking' => $trackings,
        //     'flash' =>'test123'
        // ])->with('success', 'Tracking created successfully.');
        // return Redirect::route('tracking.index')
        // ->with([
        //     'success' => '123'
        //     //'success' => 'Tracking created successfully.',
        //     //'tracking' => $trackings,
        // ]);

        // return redirect()->route('tracking.index')->with('success', 'Tracking number created successfully.');
    }

    /**
     * Remove the specified tracking number from the user's account.
     */
    public function destroy(int $id, $redirect = true): ?RedirectResponse
    {
        $tracking = Tracking::find($id);

        // Ensure the tracking number belongs to the authenticated user's account
        if (Auth::user()->account->id !== $tracking->account_id) {
            return redirect()->route('tracking.index')->with('error', 'Unauthorized action.');
        }

        if ($tracking->trackingBatch->tracking()->count() === 1) {
            $batch = $tracking->trackingBatch;

            $tracking->delete();
            $batch->delete();
        } else {
            $tracking->delete();
        }
        if ($redirect) {
            return redirect()->route('tracking.index')->with('success', 'Tracking number deleted successfully.');
        }

        return null;
    }

    public function bulkdestroy(Request $request)
    {
        $ids = $request->input('ids');
        foreach ($ids as $id) {
            $this->destroy($id, false);
        }

        return response()->json(['status' => 'success']);
    }
}
