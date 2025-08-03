<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\CheckMentionsJob;

/**
 * The application's command scheduler.
 *
 * This kernel defines the scheduled jobs and Artisan commands for the
 * application.  It registers a job to check social mentions every
 * hour.  You can add additional scheduled tasks here.
 */
class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Dispatch the job to check for social mentions hourly.  This job
        // should iterate over tenants and fetch mentions from connected
        // platforms (currently stubbed).
        $schedule->job(new CheckMentionsJob())->hourly();
        // Pull analytics metrics from social networks once per day.  The
        // FetchAnalyticsJob will iterate over connected accounts and
        // populate the engagements table with fresh numbers.
        $schedule->job(new \App\Jobs\FetchAnalyticsJob())->daily();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        // Load custom Artisan commands in the console directory.
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}