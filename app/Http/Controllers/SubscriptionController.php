<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function checkout(Request $request, String $price_id)
    {
        if (!$price_id) {
            return redirect()->back()->with('error', 'Invalid subscription plan.');
        }

        return $request->user()->newSubscription('default', $price_id)
            ->checkout([
                'success_url' => route('billing.success'),
                'cancel_url' => route('billing.failure'),
            ]);
    }

    public function success()
    {
        return to_route('tracking.index')->with('success', 'Thank you for your payment');
    }
    public function failure()
    {
        return to_route('tracking.index')->with('error', 'Something went wrong');
    }
}
