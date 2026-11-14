<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

/**
 * Laravel 11+ registers the active schedule in routes/console.php;
 * this class mirrors it for the Lab 7 documentation.
 */
class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('inventory:check-reorder')
            ->dailyAt('06:00')
            ->emailOutputTo(config('mail.admin_address'));
    }
}
