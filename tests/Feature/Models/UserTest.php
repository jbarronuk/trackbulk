<?php

namespace Tests\Feature\Models;

use App\Enums\UserType;
use App\Models\Account;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function testItHidesSensitiveAttributesFromSerialization(): void
    {
        $array = User::factory()->create([
            'client_id' => 'rm-client-id',
            'client_secret' => 'rm-secret',
        ])->toArray();

        foreach (['password', 'remember_token', 'client_id', 'client_secret', 'stripe_id', 'pm_type', 'pm_last_four'] as $hidden) {
            $this->assertArrayNotHasKey($hidden, $array);
        }
    }

    public function testItNeverLeaksTheDecryptedClientSecretInJson(): void
    {
        $user = User::factory()->create(['client_secret' => 'super-secret-value']);

        $this->assertStringNotContainsString('super-secret-value', $user->toJson());
    }

    public function testItEncryptsApiCredentialsAtRestButDecryptsOnRead(): void
    {
        $user = User::factory()->create([
            'client_id' => 'plain-id',
            'client_secret' => 'plain-secret',
        ]);

        $this->assertSame('plain-id', $user->fresh()->client_id);
        $this->assertSame('plain-secret', $user->fresh()->client_secret);

        $raw = DB::table('users')->where('id', $user->id)->first();
        $this->assertNotSame('plain-id', $raw->client_id);
        $this->assertNotSame('plain-secret', $raw->client_secret);
    }

    public function testItCastsTypeToTheUserTypeEnum(): void
    {
        $user = User::factory()->create(['type' => UserType::Admin]);

        $this->assertInstanceOf(UserType::class, $user->fresh()->type);
        $this->assertSame(UserType::Admin, $user->fresh()->type);
    }

    public function testNewUsersDefaultToTheNormalType(): void
    {
        $account = Account::factory()->create();

        $user = $account->users()->create([
            'name' => 'Test',
            'email' => 'default-type@example.com',
            'password' => 'secret',
        ]);

        $this->assertSame(UserType::Standard, $user->fresh()->type);
    }

    public function testItHashesThePassword(): void
    {
        $user = User::factory()->create(['password' => 'plain-password']);

        $this->assertNotSame('plain-password', $user->password);
        $this->assertTrue(Hash::check('plain-password', $user->password));
    }

    public function testItGuardsSensitiveColumnsFromMassAssignment(): void
    {
        $user = new User;

        $this->assertFalse($user->isFillable('account_id'));
        $this->assertFalse($user->isFillable('product_id'));
        $this->assertFalse($user->isFillable('packages_remaining'));
        $this->assertFalse($user->isFillable('type'));
    }

    public function testItAllowsTheExpectedColumnsToBeMassAssigned(): void
    {
        $user = new User;

        foreach (['name', 'email', 'password', 'client_id', 'client_secret'] as $fillable) {
            $this->assertTrue($user->isFillable($fillable));
        }
    }

    public function testItBelongsToAnAccount(): void
    {
        $account = Account::factory()->create();
        $user = User::factory()->for($account)->create();

        $this->assertInstanceOf(Account::class, $user->account);
        $this->assertSame($account->id, $user->account->id);
    }

    public function testItBelongsToAProduct(): void
    {
        $product = Product::factory()->create();
        $user = User::factory()->for($product)->create();

        $this->assertInstanceOf(Product::class, $user->product);
        $this->assertSame($product->id, $user->product->id);
    }

    public function testItMayNotHaveAProduct(): void
    {
        $user = User::factory()->create(['product_id' => null]);

        $this->assertNull($user->product);
    }

    public function testItIsCreatedWithTheAccountIdWhenMadeThroughTheRelationship(): void
    {
        $account = Account::factory()->create();

        $user = $account->users()->create([
            'name' => 'Jane',
            'email' => 'jane@example.com',
            'password' => 'secret',
        ]);

        $this->assertSame($account->id, $user->account_id);
        $this->assertTrue(Hash::check('secret', $user->fresh()->password));
    }
}