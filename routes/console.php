<?php

use App\Jobs\CreateQueryJobs;
use App\Jobs\QuotaReset;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new CreateQueryJobs)->daily();
Schedule::job(new QuotaReset)->monthlyOn(1, '00:01');
Schedule::job(new QuotaReset)->everyMinute();
// Schedule::job(new CreateQueryJobs())->everyMinute();
