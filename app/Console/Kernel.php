<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

/**
 * Lab 7 scheduler reference. Laravel 12 loads scheduled tasks from routes/console.php;
 * this Kernel class is kept as the familiar scheduling location for classroom review.
 */
class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('inventory:check-reorder-points')->dailyAt('06:00');
    }
}
