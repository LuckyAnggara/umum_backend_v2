<?php

namespace App\Console;

use App\Http\Controllers\PesanController;
use App\Http\Controllers\TempatController;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(function () {
            PesanController::reminder();
        })->dailyAt('06:00')
            ->timezone('Asia/Jakarta');

        $schedule->call(function () {
            TempatController::done();
        })->dailyAt('18:00')
            ->timezone('Asia/Jakarta');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
