<?php

namespace Tests\Feature\Jobs;

use App\Enums\TrackingStatus;
use App\Jobs\CreateRoyalMailTrackingQueryJobs as CreateQueryJobs;
use App\Jobs\QueryRoyalMailTracking;
use App\Models\Account;
use App\Models\Tracking;
use App\Models\User;
use Illuminate\Bus\PendingBatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class CreateRoyalMailTrackingQueryJobsTest extends TestCase
{
    use RefreshDatabase;

    public function testDispatchesBatchWithCreatedTrackings(): void
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
            return $batch->name === 'QueryRoyalMailTracking'
                && $batch->jobs->count() === 2
                && $batch->jobs->every(fn ($job) => $job instanceof QueryRoyalMailTracking);
        });
    }

    public function testAggregatesCreatedTrackingsAcrossUsers(): void
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

    public function testDoesNothingWhenNoUsersExist(): void
    {
        Bus::fake();

        (new CreateQueryJobs())->handle();

        Bus::assertNothingBatched();
    }

    public function testDoesNothingWhenNoCreatedTrackingsExist(): void
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

    public function testDoesNotDuplicateTrackingsWhenAccountHasMultipleUsers(): void
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