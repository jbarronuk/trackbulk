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

        //Log::info('Stripe webhook received', $payload);

        switch ($payload['type']) {
            case 'customer.subscription.created':
                $this->handleSubscriptionCreated($payload['data']['object']);
                break;
            case 'customer.subscription.updated':
                $this->handleSubscriptionUpdated($payload['data']['object']);
                break;
            case 'customer.subscription.deleted':
                $this->handleSubscriptionDeleted($payload['data']['object']);
                break;

            case 'invoice.payment_failed':
                $this->handlePaymentFailed($payload['data']['object']);
                break;
            case 'invoice.payment_succeeded':
                $this->handleInvoicePaymentSucceeded($payload['data']['object']);
                break;

            default:
                Log::warning("Unhandled webhook event: {$payload['type']}");
                break;
        }

        return response()->json(['status' => 'success']);
    }
    /**
     * Handle subscription create.
     */
    protected function handleSubscriptionCreated($subscription)
    {
        $user = User::where('stripe_id', $subscription['customer'])->first();

        if ($user) {
            $newProductId = $subscription['items']['data'][0]['price']['product'];
            $product = Product::where('stripe', $newProductId)->first();

            if ($product) {
                // Create a new subscription in your system
                $user->subscriptions()->create([
                    'type' => 'default',
                    'stripe_id' => $subscription['id'],
                    'stripe_status' => $subscription['status'],
                    'stripe_price' => $subscription['items']['data'][0]['price']['id'],
                    'quantity' => 1,
                ]);

                // Update user plan
                $user->update([
                    'product_id' => $product->id,
                    'packages_remaining' => $product->quota,
                ]);

                Log::info("New subscription created for User {$user->id} with plan {$newProductId} and quota {$product->quota}");
            } else {
                Log::warning("No matching product found for Stripe product ID: {$newProductId}");
            }
        }
    }

    /**
     * Handle subscription update.
     */
    protected function handleSubscriptionUpdated($subscription)
    {
        $user = User::where('stripe_id', $subscription['customer'])->first();

        if ($user) {
            $newProductId = $subscription['items']['data'][0]['price']['product'];
            $newPriceId = $subscription['items']['data'][0]['price']['id'];

            $product = Product::where('stripe', $newProductId)->first();

            //TODO Having the comparisong between packages remaining and the quota, will mean that if someone downgrades before the end
            //TODO add they've used all their credits, then they'll receive more credits
            if ($product) {
                $user->update([
                    'product_id' => $product->id,
                    'packages_remaining' => ($user->packages_remaining > $product->quota) ? $user->packages_remaining : $product->quota,
                    'stripe_status' => $subscription['status'],
                ]);

                // Sync with Cashier
                //$user->subscription('default')->swap($newPriceId);

                Log::info("User {$user->id} updated their subscription to plan {$newProductId} with quota {$product->quota} on pricing {$newPriceId}");
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
    protected function handleInvoicePaymentSucceeded(array $payload)
    {
        // Find the user by Stripe customer ID
        $user = \App\Models\User::where('stripe_id', $payload['customer'])->first();

        if (!$user) {
            Log::warning("User not found for Stripe customer: " . $payload['customer']);
            return response()->json(['status' => 'user_not_found'], 404);
        }

        // Update subscription status
        $subscription = $user->subscriptions()->where('stripe_id', $payload['subscription'])->first();

        if ($subscription) {
            $subscription->update([
                'stripe_status' => 'active',
            ]);
            Log::info("Subscription updated to active for user ID: " . $user->id);
        } else {
            Log::warning("Subscription not found for invoice: " . $payload['id']);
        }

        return response()->json(['status' => 'success']);
    }
}
