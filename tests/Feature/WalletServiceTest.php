<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Wallet\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class WalletServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_credit_adds_balance_and_records_transaction(): void
    {
        $user = User::factory()->create();

        $transaction = app(WalletService::class)->credit($user, '50000.00', 'topup');

        $this->assertSame('50000.00', $user->balance);
        $this->assertSame('0.00', $user->reserved_balance);
        $this->assertSame('0.00', $transaction->balance_before);
        $this->assertSame('50000.00', $transaction->balance_after);
        $this->assertDatabaseCount('wallet_transactions', 1);
    }

    public function test_hold_and_release_update_reserved_balance(): void
    {
        $user = User::factory()->create(['balance' => '50000.00']);

        app(WalletService::class)->hold($user, '15000.00');
        $this->assertSame('50000.00', $user->balance);
        $this->assertSame('15000.00', $user->reserved_balance);

        app(WalletService::class)->release($user, '10000.00');
        $this->assertSame('50000.00', $user->balance);
        $this->assertSame('5000.00', $user->reserved_balance);
    }

    public function test_charge_reduces_balance_and_reserved_balance(): void
    {
        $user = User::factory()->create([
            'balance' => '50000.00',
            'reserved_balance' => '20000.00',
        ]);

        $transaction = app(WalletService::class)->charge($user, '20000.00');

        $this->assertSame('30000.00', $user->balance);
        $this->assertSame('0.00', $user->reserved_balance);
        $this->assertSame('order_charge', $transaction->type);
    }

    public function test_wallet_rejects_insufficient_available_balance(): void
    {
        $user = User::factory()->create([
            'balance' => '10000.00',
            'reserved_balance' => '5000.00',
        ]);

        $this->expectException(RuntimeException::class);

        app(WalletService::class)->hold($user, '6000.00');
    }

    public function test_admin_adjustment_requires_reason_and_records_admin(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $user = User::factory()->create();

        $transaction = app(WalletService::class)->adjustment($user, '25000.00', 'Initial correction', $admin);

        $this->assertSame('25000.00', $user->balance);
        $this->assertSame($admin->id, $transaction->created_by_admin_id);
        $this->assertSame('Initial correction', $transaction->description);
    }
}
