<?php

namespace Tests\Feature;

use App\Enums\TrackingStatus;
use App\Enums\TrackingType;
use App\Jobs\QueryRoyalMailTracking;
use App\Models\Account;
use App\Models\Tracking;
use App\Models\User;
use Illuminate\Bus\PendingBatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class TrackingControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The Inertia pages render the root view, which pulls in Vite.
        $this->withoutVite();
    }

    /**
     * Assumes Account and User factories exist, and that a User can be linked
     * to an account via `account_id` with a `packages_remaining` balance.
     * Adjust this single helper if your factory setup differs.
     */
    private function userWithAccount(array $attributes = []): User
    {
        $account = Account::factory()->create();

        return User::factory()->create(array_merge([
            'account_id' => $account->id,
            'packages_remaining' => 10,
        ], $attributes));
    }

    public function test_guests_are_redirected_from_the_tracking_index(): void
    {
        $this->get(route('tracking.index'))->assertRedirect();
        $this->assertGuest();
    }

    public function test_index_renders_for_an_authenticated_user(): void
    {
        $this->actingAs($this->userWithAccount())
            ->get(route('tracking.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Tracking/Index')
                ->has('batches')
                ->has('statuses'),
            );
    }

    public function test_history_renders_for_an_authenticated_user(): void
    {
        $this->actingAs($this->userWithAccount())
            ->get(route('tracking.history'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Tracking/History')
                ->has('batches')
                ->has('statuses'),
            );
    }

    public function test_all_returns_json_within_a_date_range(): void
    {
        $this->actingAs($this->userWithAccount())
            ->getJson(route('tracking.all', [
                'start' => '2000-01-01 00:00:00',
                'end' => '2100-01-01 00:00:00',
                'format' => 'time',
            ]))
            ->assertOk()
            ->assertJsonCount(0);
    }

    public function test_store_creates_tracking_decrements_packages_and_dispatches_a_query_batch(): void
    {
        Bus::fake();

        $user = $this->userWithAccount(['packages_remaining' => 5]);

        $this->actingAs($user)
            ->postJson(route('tracking.store'), [
                'number' => "AB123456789GB\nCD987654321GB",
            ])
            ->assertOk()
            ->assertJsonPath('flash.success', 'Successfully added tracking numbers');

        $this->assertDatabaseHas(Tracking::class, [
            'number' => 'AB123456789GB',
            'account_id' => $user->account_id,
        ]);
        $this->assertDatabaseHas(Tracking::class, [
            'number' => 'CD987654321GB',
            'account_id' => $user->account_id,
        ]);

        // Two packages consumed: 5 -> 3.
        $this->assertDatabaseHas(User::class, [
            'id' => $user->id,
            'packages_remaining' => 3,
        ]);

        Bus::assertBatched(fn (PendingBatch $batch) => $batch->name === 'Query'
            && $batch->jobs->count() === 2
            && $batch->jobs->first() instanceof QueryRoyalMailTracking);
    }

    public function test_store_skips_duplicates_and_dispatches_no_batch_when_nothing_is_queueable(): void
    {
        Bus::fake();

        $user = $this->userWithAccount(['packages_remaining' => 5]);

        // A number that already exists and is already delivered, so it is not re-queued.
        $batch = $user->account->trackingBatches()->create([]);
        $user->account->tracking()->create([
            'number' => 'AB123456789GB',
            'type' => TrackingType::RoyalMail->value,
            'status' => TrackingStatus::RoyalMailDelivered->value,
            'tracking_batch_id' => $batch->id,
        ]);

        $this->actingAs($user)
            ->postJson(route('tracking.store'), ['number' => 'AB123456789GB'])
            ->assertOk()
            ->assertJsonPath('flash.error', 'No tracking created, tracking numbers already exist in our system');

        $this->assertSame(1, Tracking::where('account_id', $user->account_id)->count());
        $this->assertDatabaseHas(User::class, ['id' => $user->id, 'packages_remaining' => 5]);
        Bus::assertNothingBatched();
    }

    public function test_store_rejects_whitespace_only_input(): void
    {
        Bus::fake();

        $user = $this->userWithAccount();

        // Laravel's global TrimStrings + ConvertEmptyStringsToNull middleware reduce a
        // whitespace-only value to null, so it fails `required` before reaching the
        // controller. (This is also why the controller's "No valid tracking numbers
        // found" branch is effectively unreachable over HTTP.)
        $this->actingAs($user)
            ->postJson(route('tracking.store'), ['number' => "\n   \n"])
            ->assertStatus(422)
            ->assertJsonValidationErrors('number');

        $this->assertSame(0, Tracking::where('account_id', $user->account_id)->count());
        Bus::assertNothingBatched();
    }

    public function test_store_requires_a_number(): void
    {
        $this->actingAs($this->userWithAccount())
            ->postJson(route('tracking.store'), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('number');
    }

    public function test_destroy_deletes_the_users_own_tracking(): void
    {
        $user = $this->userWithAccount();
        $tracking = $this->trackingFor($user);

        $this->actingAs($user)
            ->delete(route('tracking.destroy', $tracking->id))
            ->assertRedirect(route('tracking.index'))
            ->assertSessionHas('success');

        $this->assertModelMissing($tracking);
    }

    public function test_destroy_refuses_tracking_belonging_to_another_account(): void
    {
        $owner = $this->userWithAccount();
        $tracking = $this->trackingFor($owner);

        $this->actingAs($this->userWithAccount())
            ->delete(route('tracking.destroy', $tracking->id))
            ->assertRedirect(route('tracking.index'))
            ->assertSessionHas('error');

        $this->assertModelExists($tracking);
    }

    public function test_destroy_returns_404_for_a_missing_tracking(): void
    {
        $this->actingAs($this->userWithAccount())
            ->delete(route('tracking.destroy', 999999))
            ->assertNotFound();
    }

    public function test_bulkdestroy_deletes_multiple_tracking_numbers(): void
    {
        $user = $this->userWithAccount();
        $batch = $user->account->trackingBatches()->create([]);
        $a = $this->trackingFor($user, 'AB111111111GB', $batch->id);
        $b = $this->trackingFor($user, 'CD222222222GB', $batch->id);

        $this->actingAs($user)
            ->deleteJson(route('tracking.destroy.bulk'), ['ids' => [$a->id, $b->id]])
            ->assertOk()
            ->assertJson(['status' => 'success']);

        $this->assertModelMissing($a);
        $this->assertModelMissing($b);
    }

    private function trackingFor(User $user, string $number = 'AB123456789GB', ?int $batchId = null): Tracking
    {
        $batchId ??= $user->account->trackingBatches()->create([])->id;

        return $user->account->tracking()->create([
            'number' => $number,
            'type' => TrackingType::RoyalMail->value,
            'status' => TrackingStatus::Created->value,
            'tracking_batch_id' => $batchId,
        ]);
    }
}