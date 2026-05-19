<?php

namespace Tests\Feature\Jobs;

use App\Enums\TrackingStatus;
use App\Jobs\CreateQueryJobs;
use App\Jobs\Query;
use App\Models\Account;
use App\Models\Tracking;
use App\Models\User;
use Illuminate\Bus\PendingBatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class CreateQueryJobsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispatches_batch_with_created_trackings(): void
    {
        Bus::fake();

        $account = Account::factory()->create();
        User::factory()->for($account)->create();

        Tracking::factory()
            ->count(2)
            ->create([
                'account_id' => $account->id,
                'status' => TrackingStatus::Created,
            ]);

        Tracking::factory()->create([
            'account_id' => $account->id,
            'status' => TrackingStatus::Queued,
        ]);

        (new CreateQueryJobs())->handle();

        Bus::assertBatched(function (PendingBatch $batch) {
            return $batch->name === 'Query'
                && $batch->jobs->count() === 2
                && $batch->jobs->every(fn ($job) => $job instanceof Query);
        });
    }

    public function test_aggregates_created_trackings_across_users(): void
    {
        Bus::fake();

        foreach (range(1, 2) as $_) {
            $account = Account::factory()->create();
            User::factory()->for($account)->create();
            Tracking::factory()
                ->count(2)
                ->create([
                    'account_id' => $account->id,
                    'status' => TrackingStatus::Created,
                ]);
        }

        (new CreateQueryJobs())->handle();

        Bus::assertBatched(fn (PendingBatch $batch) => $batch->jobs->count() === 4);
    }

    public function test_does_nothing_when_no_users_exist(): void
    {
        Bus::fake();

        (new CreateQueryJobs())->handle();

        Bus::assertNothingBatched();
    }

    public function test_does_nothing_when_no_created_trackings_exist(): void
    {
        Bus::fake();

        $account = Account::factory()->create();
        User::factory()->for($account)->create();

        Tracking::factory()
            ->count(3)
            ->create([
                'account_id' => $account->id,
                'status' => TrackingStatus::Queued,
            ]);

        (new CreateQueryJobs())->handle();

        Bus::assertNothingBatched();
    }

    public function test_does_not_duplicate_trackings_when_account_has_multiple_users(): void
    {
        Bus::fake();

        $account = Account::factory()->create();
        User::factory()->for($account)->count(2)->create();

        Tracking::factory()->create([
            'account_id' => $account->id,
            'status' => TrackingStatus::Created,
        ]);

        (new CreateQueryJobs())->handle();

        Bus::assertBatched(fn (PendingBatch $batch) => $batch->jobs->count() === 1);
    }
}