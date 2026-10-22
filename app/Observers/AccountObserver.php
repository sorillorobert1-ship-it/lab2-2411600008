<?php

namespace App\Observers;

use App\Models\BankTransaction;
use App\Notifications\LowBalanceNotification;
use Illuminate\Validation\ValidationException;

class AccountObserver
{
    public function created(BankTransaction $transaction): void
    {
        $account = $transaction->account()
            ->lockForUpdate()
            ->firstOrFail();

        $oldBalance = (string) $account->balance;
        $amount = (string) $transaction->amount;

        if (bccomp($amount, '0', 2) <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'Amount must be greater than zero.'
            ]);
        }

        if (in_array($transaction->type, ['deposit', 'transfer_in'], true)) {
            $newBalance = bcadd($oldBalance, $amount, 2);

        } elseif (in_array($transaction->type, ['withdrawal', 'transfer_out'], true)) {
            $newBalance = bcsub($oldBalance, $amount, 2);

            if (bccomp($newBalance, '0', 2) < 0) {
                throw ValidationException::withMessages([
                    'amount' => 'Insufficient funds.'
                ]);
            }

        } else {
            throw ValidationException::withMessages([
                'type' => 'Invalid transaction type.'
            ]);
        }

        $account->balance = $newBalance;
        $account->saveQuietly();

        $transaction->forceFill([
            'balance_after' => $newBalance,
        ])->saveQuietly();

        // Alert whenever the resulting balance is at or below the threshold.
        if (bccomp($newBalance, (string) $account->low_balance_threshold, 2) <= 0) {
            $account->user->notify(
                new LowBalanceNotification($account)
            );
        }
    }
}
