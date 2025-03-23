<?php

namespace App\Jobs;

use App\Enums\TrackingStatus;
use App\Models\User;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;

class CreateQueryJobs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;
    
    public function __construct()
    {
        $this->onQueue('query'); // This sets the queue dynamically
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $users = User::all();
        $jobs = [];

        foreach($users as $user) {
            $trackings = $user->account->tracking()->orderBy('created_at', 'desc')->get();

            $queued = $trackings->where('status', TrackingStatus::Created);
        
            foreach($queued as $job) {
                $jobs[] = (new Query($job))->delay(now()->addSeconds(10));
            }
        }

        Bus::batch($jobs)->name('Query')->onQueue('Query')->dispatch();
    }
}
