<?php

use App\Http\Controllers\TransactionController;
use App\Http\Controllers\AuthController;
use App\Models\Account;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', fn () => Auth::check() ? redirect()->route('dashboard') : view('welcome'));
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [TransactionController::class, 'index'])->name('dashboard');
    Route::get('/transactions/create', [TransactionController::class, 'create'])->name('transactions.create');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
});

Route::get('/api/accounts/{account}/balance', function (Account $account) {
    abort_unless($account->user_id === auth()->id(), 403);
    return response()->json([
        'account_number' => $account->account_number,
        'balance' => (float) $account->balance,
        'low_balance' => (float) $account->balance < (float) $account->low_balance_threshold,
        'threshold' => (float) $account->low_balance_threshold,
        'updated_at' => optional($account->updated_at)->toISOString(),
    ]);
})->middleware('auth')->name('api.accounts.balance');
