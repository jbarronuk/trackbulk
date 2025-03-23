<?php

namespace App\Http\Controllers;

use App\Enums\AccountQuota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;
use App\Models\User;
use App\Models\Product;
use App\Notifications\SubscriptionChangedNotification;

class StripeWebhookController extends CashierController
{
    /**
     * Handle incoming Stripe webhooks.
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->all();

        Log::info('Stripe webhook received', $payload);

        switch ($payload['type']) {
            case 'customer.subscription.created':
            case 'customer.subscription.updated':
                $this->handleSubscriptionUpdated($payload['data']['object']);
                break;

            case 'customer.subscription.deleted':
                $this->handleSubscriptionDeleted($payload['data']['object']);
                break;

            case 'invoice.payment_failed':
                $this->handlePaymentFailed($payload['data']['object']);
                break;

            default:
                Log::warning("Unhandled webhook event: {$payload['type']}");
                break;
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Handle subscription creation and updates.
     */
    protected function handleSubscriptionUpdated($subscription)
    {
        $user = User::where('stripe_id', $subscription['customer'])->first();

        if ($user) {
            $newProductId = $subscription['items']['data'][0]['price']['product'];

            $product = Product::where('stripe', $newProductId)->first();

            if ($product) {
                $user->update([
                    'plan' => $newProductId,
                    'packages_remaining' => $product->quota,
                    'stripe_status' => $subscription['status'],
                ]);

                // Sync with Cashier
                $user->subscription('default')->swap($newProductId);

                Log::info("User {$user->id} updated their subscription to plan {$newProductId} with quota {$product->quota}");
            } else {
                Log::warning("No matching product found for Stripe product ID: {$newProductId}");
            }
        }
    }

    /**
     * Handle subscription cancellations.
     */
    protected function handleSubscriptionDeleted($subscription)
    {
        $user = User::where('stripe_id', $subscription['customer'])->first();

        if ($user) {
            $user->update([
                'plan' => null,
                'packages_remaining' => AccountQuota::Free->value,
                'stripe_status' => 'canceled',
            ]);

            Log::info("User {$user->id} canceled their subscription");
        }
    }

    /**
     * Handle payment failures.
     */
    protected function handlePaymentFailed($invoice)
    {
        $user = User::where('stripe_id', $invoice['customer'])->first();

        if ($user) {
            $user->update([
                'plan' => null,
                'packages_remaining' => AccountQuota::Free->value,
                'stripe_status' => 'canceled',
            ]);

            Log::warning("Payment failed for user {$user->id}");
        }
    }
}
