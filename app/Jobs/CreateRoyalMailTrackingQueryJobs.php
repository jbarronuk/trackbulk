<?php

namespace App\Jobs;

use App\Enums\TrackingStatus;
use App\Models\Account;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;

class CreateRoyalMailTrackingQueryJobs implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const DELAY_SECONDS = 5;

    public function __construct()
    {
        $this->onQueue('Query');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $jobs = [];

        Account::with(['tracking' => fn ($q) => $q->where('status', TrackingStatus::Created->value)])
            ->each(function (Account $account) use (&$jobs) {
                foreach ($account->tracking as $tracking) {
                    $jobs[] = (new QueryRoyalMailTracking($tracking))->delay(now()->addSeconds(self::DELAY_SECONDS * (count($jobs) + 1)));
                }
            });

        if (empty($jobs)) {
            return;
        }

        Bus::batch($jobs)->name('QueryRoyalMailTracking')->onQueue('Query')->dispatch();
    }
}