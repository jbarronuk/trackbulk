<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;

class SubscriptionController extends Controller
{
    public function checkout(Request $request, string $price_id): RedirectResponse
    {
        if (! in_array($price_id, Arr::flatten(config('subscriptions.stripe_prices')), true)) {
            return redirect()->back()->with('error', 'Invalid subscription plan.');
        }

        try {
            $session = $request->user()->newSubscription('default', $price_id)->trialDays(7)
                ->checkout([
                    'success_url' => route('billing.success'),
                    'cancel_url' => route('billing.failure'),
                ]);
        } catch (\Exception $e) {
            report($e);
            return redirect()->back()->with('error', 'Could not start checkout. Please try again.');
        }
        $url = $session->asStripeCheckoutSession()->url;

        return redirect()->to($url);
    }

    public function success(): RedirectResponse
    {
        return to_route('tracking.index')->with('success', 'Thank you for subscribing');
    }

    public function failure(): RedirectResponse
    {
        return to_route('tracking.index')->with('error', 'Something went wrong');
    }
}
