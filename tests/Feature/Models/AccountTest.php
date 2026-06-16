<?php

namespace Tests\Feature\Models;

use App\Enums\TrackingStatus;
use App\Enums\TrackingType;
use App\Exceptions\MissingApiCredentialsException;
use App\Models\Account;
use App\Models\Tracking;
use App\Models\TrackingBatch;
use App\Models\User;
use App\Services\RoyalMail\RoyalMailCredentials;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    public function testItResolvesRoyalMailCredentialsFromAUserWithBothCredentials(): void
    {
        $account = Account::factory()->create();
        User::factory()->for($account)->create([
            'client_id' => 'rm-client-id',
            'client_secret' => 'rm-secret',
        ]);

        $credentials = $account->royalMailCredentials();

        $this->assertInstanceOf(RoyalMailCredentials::class, $credentials);
        $this->assertSame('rm-client-id', $credentials->clientId);
        $this->assertSame('rm-secret', $credentials->clientSecret);
    }

    public function testItResolvesCredentialsEvenWhenOtherUsersLackThem(): void
    {
        $account = Account::factory()->create();
        User::factory()->for($account)->create(['client_id' => null, 'client_secret' => null]);
        User::factory()->for($account)->create([
            'client_id' => 'real-id',
            'client_secret' => 'real-secret',
        ]);

        $credentials = $account->royalMailCredentials();

        $this->assertSame('real-id', $credentials->clientId);
        $this->assertSame('real-secret', $credentials->clientSecret);
    }

    public function testItThrowsWhenTheAccountHasNoUsers(): void
    {
        $account = Account::factory()->create();

        $this->expectException(MissingApiCredentialsException::class);

        $account->royalMailCredentials();
    }

    public function testItThrowsWhenNoUserHasCredentials(): void
    {
        $account = Account::factory()->create();
        User::factory()->for($account)->create(['client_id' => null, 'client_secret' => null]);

        $this->expectException(MissingApiCredentialsException::class);

        $account->royalMailCredentials();
    }

    public function testItIgnoresUsersMissingEitherCredential(): void
    {
        $account = Account::factory()->create();
        User::factory()->for($account)->create(['client_id' => 'id-only', 'client_secret' => null]);
        User::factory()->for($account)->create(['client_id' => null, 'client_secret' => 'secret-only']);

        $this->expectException(MissingApiCredentialsException::class);

        $account->royalMailCredentials();
    }

    public function testItOnlyConsidersUsersBelongingToTheAccount(): void
    {
        $account = Account::factory()->create();

        User::factory()->for(Account::factory())->create([
            'client_id' => 'other-id',
            'client_secret' => 'other-secret',
        ]);

        $this->expectException(MissingApiCredentialsException::class);

        $account->royalMailCredentials();
    }

    public function testItHasManyUsers(): void
    {
        $account = Account::factory()->create();
        User::factory()->count(2)->for($account)->create();
        User::factory()->for(Account::factory())->create();

        $this->assertCount(2, $account->users);
        $this->assertInstanceOf(User::class, $account->users->first());
    }

    public function testItHasManyTracking(): void
    {
        $account = Account::factory()->create();
        $account->tracking()->createMany([
            ['number' => 'AB100', 'type' => TrackingType::RoyalMail->value, 'status' => TrackingStatus::Created->value],
            ['number' => 'AB200', 'type' => TrackingType::RoyalMail->value, 'status' => TrackingStatus::Created->value],
        ]);

        $this->assertCount(2, $account->tracking);
        $this->assertInstanceOf(Tracking::class, $account->tracking->first());
    }

    public function testItHasManyTrackingBatches(): void
    {
        $account = Account::factory()->create();
        $account->trackingBatches()->create([]);
        $account->trackingBatches()->create([]);

        $this->assertCount(2, $account->trackingBatches);
        $this->assertInstanceOf(TrackingBatch::class, $account->trackingBatches->first());
    }
}