<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Schedule delivery location fetch jobs to run daily at 5:00 AM
        $schedule->job(new \App\Jobs\FetchOverseasDeliveryLocations)
            ->dailyAt('05:00')
            ->withoutOverlapping()
            ->onOneServer();

        $schedule->job(new \App\Jobs\FetchDpdDeliveryLocations)
            ->dailyAt('05:00')
            ->withoutOverlapping()
            ->onOneServer();

        $schedule->job(new \App\Jobs\FetchHpDeliveryLocations)
            ->dailyAt('05:00')
            ->withoutOverlapping()
            ->onOneServer();

        $schedule->job(new \App\Jobs\FetchGlsDeliveryLocations)
            ->dailyAt('05:00')
            ->withoutOverlapping()
            ->onOneServer();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
