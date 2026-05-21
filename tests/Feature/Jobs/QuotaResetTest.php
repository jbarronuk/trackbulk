<?php

namespace Tests\Feature\Jobs;

use App\Enums\AccountQuota;
use App\Jobs\QuotaReset;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class QuotaResetTest extends TestCase
{
    use RefreshDatabase;

    public function testUsersWithoutAProductGetTheFreeQuota(): void
    {
        $user = User::factory()->create([
            'product_id' => null,
            'packages_remaining' => 0,
        ]);

        (new QuotaReset)->handle();

        $this->assertSame(
            AccountQuota::Free->value,
            $user->fresh()->packages_remaining,
        );
    }

    public function testUsersWithAProductGetTheirProductQuota(): void
    {
        $product = Product::factory()->create(['quota' => 50]);
        $user = User::factory()->create([
            'product_id' => $product->id,
            'packages_remaining' => 0,
        ]);

        (new QuotaReset)->handle();

        $this->assertSame(50, $user->fresh()->packages_remaining);
    }

    public function testItResetsQuotasForMixedBatchOfUsers(): void
    {
        $product = Product::factory()->create(['quota' => 25]);
        $paid = User::factory()->count(3)->create(['product_id' => $product->id]);
        $free = User::factory()->count(2)->create(['product_id' => null]);

        (new QuotaReset)->handle();

        foreach ($paid as $user) {
            $this->assertSame(25, $user->fresh()->packages_remaining);
        }
        foreach ($free as $user) {
            $this->assertSame(
                AccountQuota::Free->value,
                $user->fresh()->packages_remaining,
            );
        }
    }

    public function testItOverwritesExistingQuotaValues(): void
    {
        $user = User::factory()->create([
            'product_id' => null,
            'packages_remaining' => 999,
        ]);

        (new QuotaReset)->handle();

        $this->assertSame(
            AccountQuota::Free->value,
            $user->fresh()->packages_remaining,
        );
    }

    public function testItLogsStartAndCompletion(): void
    {
        Log::spy();
        User::factory()->count(2)->create(['product_id' => null]);

        (new QuotaReset)->handle();

        Log::shouldHaveReceived('info')
            ->with('Quota reset started.')
            ->once();

        Log::shouldHaveReceived('info')
            ->with('Quota reset complete.', \Mockery::on(fn ($ctx) =>
                $ctx['processed'] === 2 && $ctx['failed'] === 0
            ))
            ->once();
    }

    public function testItProcessesUsersInChunksWithoutRunningOutOfMemory(): void
    {
        User::factory()->count(1200)->create(['product_id' => null]);

        (new QuotaReset)->handle();

        $this->assertSame(
            1200,
            User::where('packages_remaining', AccountQuota::Free->value)->count(),
        );
    }

    public function testItCanBeDispatchedToTheQueue(): void
    {
        \Illuminate\Support\Facades\Bus::fake();

        QuotaReset::dispatch();

        \Illuminate\Support\Facades\Bus::assertDispatched(QuotaReset::class);
    }
}