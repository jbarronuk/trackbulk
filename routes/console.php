<?php

use App\Jobs\CreateQueryJobs;
use App\Jobs\QuotaReset;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new CreateQueryJobs)->daily()->name('Create the jobs for the queries');
Schedule::job(new QuotaReset)->monthlyOn(1, '00:01')->name('quota-reset');
