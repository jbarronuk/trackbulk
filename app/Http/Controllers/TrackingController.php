<?php

namespace App\Http\Controllers;

use App\Enums\TrackingStatus;
use App\Enums\TrackingType;
use App\Jobs\Query;
use App\Jobs\QueryRoyalMail;
use App\Models\Tracking;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class TrackingController extends Controller
{
    /**
     * Display a listing of the tracking numbers for the authenticated user's account.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $trackings = $user->account->tracking()->orderBy('created_at', 'desc')->get();

        return Inertia::render('Tracking/Index', [
            'tracking' => $trackings,
            'statuses' => TrackingStatus::all(),            
        ]);
    }
    public function all()
    {
        $user = Auth::user();

        $trackings = $user->account->tracking()->orderBy('created_at', 'desc')->get();
        return $trackings;
    }

    /**
     * Store a newly created tracking number in the user's account.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'number' => 'required|string',
        ]);
        $numbers = array_values(array_filter(array_map('trim', explode("\n", $validated['number']))));

        $user = Auth::user();

        $alreadyExisting = [];
        $depleted = false;
        foreach($numbers as $number) {
            if ($user->packages_remaining > 0) {
                $existing = $user->account->tracking()->where('number', $number)->first();
                if (is_null($existing)) {
                    $user->account->tracking()->create([
                        'number' => $number,
                        'type' => TrackingType::RoyalMail->value,
                        'status' => TrackingStatus::Created->value,
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
        foreach($queued as $job) {
            $jobs[] = (new Query($job))->delay(now()->addSeconds(10));
        }

        $batch = Bus::batch($jobs)->name('Query')->onQueue('Query')->dispatch();

        if (count($alreadyExisting) === count($numbers)) {
            $flash = ['error' => 'No tracking created, tracking numbers already existing in our system'];
        } else if ($depleted) {
            $flash = [
                'error'     => 'Tracking numbers not added as the number of available tracking numbers for this month has been depleted'
            ];
        } else if (count($alreadyExisting) > 0) {
            $flash = [
                'success'   => 'Successfully added tracking numbers',
                'error'     => 'Some tracking number not added as they already existing in our system'
            ];
        } else {
            $flash = ['success'   => 'Successfull added tracking numbers'];
        }

        // [
        //     'success' => 'Tracking created successfully.',
        //     'error' => 'Tracking created successfully.',
        // ],

        return response()->json([
            'flash' => $flash,
            'tracking' => $trackings
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

        //return redirect()->route('tracking.index')->with('success', 'Tracking number created successfully.');
    }

    /**
     * Remove the specified tracking number from the user's account.
     *
     * @param  \App\Models\Tracking  $tracking
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $tracking = Tracking::find($id);
        // Ensure the tracking number belongs to the authenticated user's account
        if (Auth::user()->account->id !== $tracking->account_id) {
            return redirect()->route('tracking.index')->with('error', 'Unauthorized action.');
        }

        // Delete the tracking
        $tracking->delete();

        return redirect()->route('tracking.index')->with('success', 'Tracking number deleted successfully.');
    }
}
