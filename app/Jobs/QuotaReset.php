<?php

namespace App\Jobs;

use App\Enums\AccountQuota;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class QuotaReset implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Quota reset started.');

        $processed = 0;
        $failed = 0;

        User::with('product:id,quota')
            ->chunkById(500, function ($users) use (&$processed, &$failed) {
                foreach ($users as $user) {
                    try {
                        $user->packages_remaining = $user->product_id
                            ? $user->product->quota
                            : AccountQuota::Free->value;
                        $user->save();
                        $processed++;
                    } catch (Throwable $e) {
                        $failed++;
                        Log::error('Quota reset failed for user.', [
                            'user_id' => $user->id,
                            'exception' => $e->getMessage(),
                        ]);
                    }
                }
            });

        Log::info('Quota reset complete.', [
            'processed' => $processed,
            'failed' => $failed,
        ]);
    }
}