<?php

namespace App\Providers;

use App\Models\BankTransaction;
use App\Observers\BankTransactionObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        BankTransaction::observe(BankTransactionObserver::class);
    }
}
