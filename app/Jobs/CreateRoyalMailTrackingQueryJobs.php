<?php

namespace App\Jobs;

use App\Enums\TrackingStatus;
use App\Models\Account;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Bus;

class CreateRoyalMailTrackingQueryJobs implements ShouldQueue
{
    use Queueable;

    private const DELAY_SECONDS = 5;
    private const QUEUE = 'Query';

    public function __construct()
    {
        $this->onQueue(self::QUEUE);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $jobs = [];

        $createdTracking = fn ($query) => $query->where('status', TrackingStatus::Created->value);
 
        Account::whereHas('tracking', $createdTracking)
            ->with(['tracking' => $createdTracking])
            ->each(function (Account $account) use (&$jobs) {
                foreach ($account->tracking as $tracking) {
                    $jobs[] = (new QueryRoyalMailTracking($tracking))
                        ->delay(now()->addSeconds(self::DELAY_SECONDS * (count($jobs) + 1)));
                }
            });

        if (empty($jobs)) {
            return;
        }

        Bus::batch($jobs)->name('QueryRoyalMailTracking')->onQueue(self::QUEUE)->dispatch();
    }
}