<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('crm:send-task-reminders')->everyMinute()->withoutOverlapping();
        $schedule->command('crm:send-lead-followup-reminders')->everyMinute()->withoutOverlapping();
        $schedule->command('crm:dispatch-scheduled-campaigns')->everyMinute()->withoutOverlapping();

        // Runs queued jobs (campaign emails especially) once a minute and
        // exits as soon as the queue is empty — no persistent worker
        // process needed, since this piggybacks on the same Task Scheduler
        // entry that already runs `schedule:run` every minute. --max-time
        // is a safety net so one slow minute can't overlap the next.
        $schedule->command('queue:work --queue=default --stop-when-empty --max-time=50 --tries=3')
            ->everyMinute()
            ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
