<?php

namespace Tests\Feature\Models;

use App\Models\Account;
use App\Models\Tracking;
use App\Models\TrackingBatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackingBatchTest extends TestCase
{
    use RefreshDatabase;

    public function testItBelongsToAnAccount(): void
    {
        $account = Account::factory()->create();
        $batch = $account->trackingBatches()->create([]);

        $this->assertInstanceOf(Account::class, $batch->account);
        $this->assertSame($account->id, $batch->account->id);
        $this->assertSame($account->id, $batch->account_id);
    }

    public function testItHasManyTrackingLinkedOnTheTrackingBatchIdColumn(): void
    {
        $account = Account::factory()->create();
        $batch = $account->trackingBatches()->create([]);

        $tracking = Tracking::factory()
            ->for($account)
            ->for($batch, 'trackingBatch')
            ->create();

        $this->assertCount(1, $batch->tracking);
        $this->assertInstanceOf(Tracking::class, $batch->tracking->first());
        $this->assertSame($tracking->id, $batch->tracking->first()->id);


        $this->assertSame($batch->id, $tracking->tracking_batch_id);
    }

    public function testItOnlyReturnsTrackingForThisBatch(): void
    {
        $account = Account::factory()->create();
        $batch = $account->trackingBatches()->create([]);
        $otherBatch = $account->trackingBatches()->create([]);

        Tracking::factory()->for($account)->for($batch, 'trackingBatch')->create();
        Tracking::factory()->for($account)->for($otherBatch, 'trackingBatch')->create();

        $this->assertCount(1, $batch->tracking);
    }
}