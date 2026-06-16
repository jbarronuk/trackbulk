<?php

namespace Tests\Feature\Models;

use App\Enums\TrackingStatus;
use App\Enums\TrackingType;
use App\Models\Account;
use App\Models\Tracking;
use App\Models\TrackingBatch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackingTest extends TestCase
{
    use RefreshDatabase;

    public function testItCastsStatusToTheTrackingStatusEnum(): void
    {
        $tracking = Tracking::factory()->create(['status' => TrackingStatus::Unknown]);

        $this->assertInstanceOf(TrackingStatus::class, $tracking->fresh()->status);
        $this->assertSame(TrackingStatus::Unknown, $tracking->fresh()->status);
    }

    public function testItCastsTypeToTheTrackingTypeEnum(): void
    {
        $tracking = Tracking::factory()->create(['type' => TrackingType::RoyalMail]);

        $this->assertInstanceOf(TrackingType::class, $tracking->fresh()->type);
        $this->assertSame(TrackingType::RoyalMail, $tracking->fresh()->type);
    }

    public function testItCoercesABackingValueAssignedToStatusIntoTheEnum(): void
    {
        // Documents the behaviour we rely on: the cast accepts the scalar on write.
        $tracking = new Tracking;
        $tracking->status = TrackingStatus::Unknown->value;

        $this->assertSame(TrackingStatus::Unknown, $tracking->status);
    }

    public function testItGuardsOwnershipAndForeignKeyColumnsFromMassAssignment(): void
    {
        $tracking = new Tracking;

        $this->assertFalse($tracking->isFillable('account_id'));
        $this->assertFalse($tracking->isFillable('tracking_batch_id'));
    }

    public function testItAllowsTheIntendedColumnsToBeFilled(): void
    {
        $tracking = new Tracking;

        $this->assertTrue($tracking->isFillable('number'));
        $this->assertTrue($tracking->isFillable('status'));
        $this->assertTrue($tracking->isFillable('type'));
        $this->assertTrue($tracking->isFillable('response'));
        $this->assertTrue($tracking->isFillable('summary_response'));
    }

    public function testItDropsAGuardedColumnPassedToFill(): void
    {
        if (Model::preventsSilentlyDiscardingAttributes()) {
            $this->markTestSkipped('Strict mode is on — fill() throws instead of silently dropping.');
        }

        $tracking = (new Tracking)->fill(['number' => 'ABC123', 'account_id' => 999]);

        $this->assertSame('ABC123', $tracking->number);
        $this->assertNull($tracking->account_id);
    }

    public function testItBelongsToAnAccountOnAccountId(): void
    {
        $account = Account::factory()->create();
        $tracking = Tracking::factory()->for($account)->create();

        $this->assertInstanceOf(Account::class, $tracking->account);
        $this->assertSame($account->id, $tracking->account->id);
        $this->assertSame($account->id, $tracking->account_id);
    }

    public function testItBelongsToATrackingBatchOnTrackingBatchId(): void
    {
        $batch = TrackingBatch::factory()->create();
        $tracking = Tracking::factory()->for($batch, 'trackingBatch')->create();

        $this->assertInstanceOf(TrackingBatch::class, $tracking->trackingBatch);
        $this->assertSame($batch->id, $tracking->trackingBatch->id);
        $this->assertSame($batch->id, $tracking->tracking_batch_id);
    }
}