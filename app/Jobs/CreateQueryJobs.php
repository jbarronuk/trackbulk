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

class CreateQueryJobs implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const DELAY_SECONDS = 10;

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
                    $jobs[] = (new Query($tracking))->delay(now()->addSeconds(self::DELAY_SECONDS));
                }
            });

        if (empty($jobs)) {
            return;
        }

        Bus::batch($jobs)->name('Query')->onQueue('Query')->dispatch();
    }
}