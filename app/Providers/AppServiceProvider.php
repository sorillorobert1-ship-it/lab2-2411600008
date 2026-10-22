<?php

namespace App\Providers;

use App\Models\BankTransaction;
use App\Observers\AccountObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        BankTransaction::observe(AccountObserver::class);
    }
}
