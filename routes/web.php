<?php

use App\Http\Controllers\ProfileController;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\StripeWebhookController;

Route::get('/', [SiteController::class, 'index'])->name('site.index');
Route::get('/plans', [SiteController::class, 'plans'])->name('site.plans');
Route::get('/contact', [SiteController::class, 'contact'])->name('site.contact');
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook'])->name('cashier.webhook');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/tracking', [TrackingController::class, 'index'])->name('tracking.index');
    Route::get('/api/tracking', [TrackingController::class, 'all'])->name('tracking.all');

    Route::post('/tracking', [TrackingController::class, 'store'])->name('tracking.store');
    Route::delete('/tracking/{id}', [TrackingController::class, 'destroy'])->name('tracking.destroy');

    Route::get('/checkout/{price_id}', [SubscriptionController::class, 'checkout'])->name('billing.checkout');
    Route::get('/billing/success', [SubscriptionController::class, 'success'])->name('billing.success');
    Route::get('/billing/failure', [SubscriptionController::class, 'failure'])->name('billing.failure');
});

require __DIR__.'/auth.php';
