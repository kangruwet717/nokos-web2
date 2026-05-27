<?php

namespace App\Services\Wallet;

use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class WalletService
{
    public function credit(
        User $user,
        string $amount,
        string $type = 'topup',
        ?Model $reference = null,
        ?string $description = null,
        array $metadata = [],
        ?User $admin = null,
    ): WalletTransaction {
        return $this->mutate($user, $amount, $type, 'credit', 'balance_credit', $reference, $description, $metadata, $admin);
    }

    public function debit(
        User $user,
        string $amount,
        string $type = 'adjustment',
        ?Model $reference = null,
        ?string $description = null,
        array $metadata = [],
        ?User $admin = null,
    ): WalletTransaction {
        return $this->mutate($user, $amount, $type, 'debit', 'balance_debit', $reference, $description, $metadata, $admin);
    }

    public function hold(
        User $user,
        string $amount,
        string $type = 'order_hold',
        ?Model $reference = null,
        ?string $description = null,
        array $metadata = [],
    ): WalletTransaction {
        return $this->mutate($user, $amount, $type, 'hold', 'reserved_hold', $reference, $description, $metadata);
    }

    public function release(
        User $user,
        string $amount,
        string $type = 'refund',
        ?Model $reference = null,
        ?string $description = null,
        array $metadata = [],
    ): WalletTransaction {
        return $this->mutate($user, $amount, $type, 'release', 'reserved_release', $reference, $description, $metadata);
    }

    public function charge(
        User $user,
        string $amount,
        string $type = 'order_charge',
        ?Model $reference = null,
        ?string $description = null,
        array $metadata = [],
    ): WalletTransaction {
        return $this->mutate($user, $amount, $type, 'debit', 'reserved_charge', $reference, $description, $metadata);
    }

    public function adjustment(User $user, string $amount, string $reason, User $admin): WalletTransaction
    {
        if (trim($reason) === '') {
            throw new InvalidArgumentException('Adjustment reason is required.');
        }

        if (str_starts_with($amount, '-')) {
            return $this->debit($user, ltrim($amount, '-'), 'adjustment', null, $reason, [], $admin);
        }

        return $this->credit($user, $amount, 'adjustment', null, $reason, [], $admin);
    }

    private function mutate(
        User $user,
        string $amount,
        string $type,
        string $direction,
        string $operation,
        ?Model $reference = null,
        ?string $description = null,
        array $metadata = [],
        ?User $admin = null,
    ): WalletTransaction {
        $this->assertPositiveAmount($amount);

        return DB::transaction(function () use ($user, $amount, $type, $direction, $operation, $reference, $description, $metadata, $admin) {
            /** @var User $lockedUser */
            $lockedUser = User::query()->whereKey($user->getKey())->lockForUpdate()->firstOrFail();

            $balanceBefore = (string) $lockedUser->balance;
            $reservedBefore = (string) $lockedUser->reserved_balance;
            [$balanceAfter, $reservedAfter] = $this->calculate($operation, $balanceBefore, $reservedBefore, $amount);

            if (bccomp($balanceAfter, '0', 2) < 0 || bccomp($reservedAfter, '0', 2) < 0) {
                throw new RuntimeException('Wallet balances cannot be negative.');
            }

            if (bccomp($balanceAfter, $reservedAfter, 2) < 0) {
                throw new RuntimeException('Available balance is insufficient.');
            }

            $lockedUser->forceFill([
                'balance' => $balanceAfter,
                'reserved_balance' => $reservedAfter,
            ])->save();

            $transaction = WalletTransaction::create([
                'user_id' => $lockedUser->id,
                'type' => $type,
                'direction' => $direction,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reserved_before' => $reservedBefore,
                'reserved_after' => $reservedAfter,
                'reference_type' => $reference?->getMorphClass(),
                'reference_id' => $reference?->getKey(),
                'status' => 'success',
                'description' => $description,
                'metadata' => $metadata ?: null,
                'created_by_admin_id' => $admin?->id,
            ]);

            $user->refresh();

            return $transaction;
        });
    }

    private function calculate(string $operation, string $balance, string $reserved, string $amount): array
    {
        return match ($operation) {
            'balance_credit' => [bcadd($balance, $amount, 2), $reserved],
            'balance_debit' => [bcsub($balance, $amount, 2), $reserved],
            'reserved_hold' => [$balance, bcadd($reserved, $amount, 2)],
            'reserved_release' => [$balance, bcsub($reserved, $amount, 2)],
            'reserved_charge' => [bcsub($balance, $amount, 2), bcsub($reserved, $amount, 2)],
            default => throw new InvalidArgumentException("Unsupported wallet operation [{$operation}]."),
        };
    }

    private function assertPositiveAmount(string $amount): void
    {
        if (! is_numeric($amount) || bccomp($amount, '0', 2) <= 0) {
            throw new InvalidArgumentException('Wallet amount must be greater than zero.');
        }
    }
}
