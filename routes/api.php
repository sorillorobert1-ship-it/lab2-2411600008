<?php

use App\Models\Account;
use Illuminate\Support\Facades\Route;

// The web middleware keeps the existing session-based login available to
// the AJAX polling request while this endpoint remains under /api.
Route::middleware(['web', 'auth'])->group(function (): void {
    Route::get('/accounts/{account}/balance', function (Account $account) {
        abort_unless($account->user_id === auth()->id(), 403);

        return response()->json([
            'account_number' => $account->account_number,
            'balance' => (float) $account->balance,
            'low_balance' => (float) $account->balance < (float) $account->low_balance_threshold,
            'threshold' => (float) $account->low_balance_threshold,
            'updated_at' => optional($account->updated_at)->toISOString(),
        ]);
    })->name('api.accounts.balance');
});
