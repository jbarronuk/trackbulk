<?php

use App\Jobs\CreateRoyalMailTrackingQueryJobs;
use App\Jobs\QuotaReset;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new CreateRoyalMailTrackingQueryJobs)->daily()->name('Create the jobs for the queries');
Schedule::job(new QuotaReset)->monthlyOn(1, '00:01')->name('quota-reset');
