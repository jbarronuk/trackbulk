<?php

namespace App\Jobs;

use App\Enums\AccountQuota;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class QuotaReset
{
    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Quota Reset');
        $users = User::all();
        foreach ($users as $user) {
            if (! is_null($user->product_id)) {
                $user->packages_remaining = $user->product->quota;
            } else {
                $user->packages_remaining = AccountQuota::Free->value;
            }
            $user->save();
        }
    }
}
