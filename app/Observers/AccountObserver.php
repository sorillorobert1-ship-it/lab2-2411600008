<?php

namespace App\Observers;

use App\Models\BankTransaction;
use App\Notifications\LowBalanceNotification;

class AccountObserver
{
    public function created(BankTransaction $transaction): void
    {
        $account = $transaction->account()->lockForUpdate()->firstOrFail();

        $delta = in_array($transaction->type, ['deposit', 'transfer_in'], true)
            ? $transaction->amount
            : -$transaction->amount;

        $account->balance = bcadd((string) $account->balance, (string) $delta, 2);
        $account->saveQuietly();

        $transaction->forceFill([
            'balance_after' => $account->balance,
        ])->saveQuietly();

        if ((float) $account->balance < (float) $account->low_balance_threshold) {
            $account->user->notify(new LowBalanceNotification($account));
        }
    }
}
