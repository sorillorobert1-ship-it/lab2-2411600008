<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

/**
 * Scheduler reference for the Lab 7 documentation checklist.
 *
 * Laravel 11 boots the same schedule from routes/console.php.
 */
class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('inventory:check-reorder-points')->dailyAt('06:00');
    }
}
