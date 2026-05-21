<?php

namespace Tests\Feature\Console;

use Illuminate\Console\Scheduling\Schedule;
use Tests\TestCase;

class ScheduleTest extends TestCase
{
    public function testQuotaResetIsScheduledMonthly(): void
    {
        $schedule = app(Schedule::class);

        $events = collect($schedule->events())->filter(
            fn ($event) => $event->description === 'quota-reset',
        );

        $this->assertCount(1, $events, 'Expected exactly one quota-reset schedule.');
        $this->assertSame('1 0 1 * *', $events->first()->expression);
    }
}