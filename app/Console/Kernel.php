<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        //  \spresnac\createcliuser\CreateCliUserCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        if (config('app.env') == 'production') {
            // Send monthly report
            $schedule->command('job:send-monthly-report')
                ->monthlyOn(1, '04:00'); //UTC time
            // Deactivate expired users
            $schedule->command('job:deactivate-expired-users')
                ->dailyAt('06:00'); //UTC time
            // Remind users with expiring activations
            $schedule->command('job:send-remind-expiring-users')
                ->dailyAt('08:00'); //UTC time
            // Send reactivation tokens
            $schedule->command('job:send-reactivation-token')
                ->dailyAt('10:00'); //UTC time
        }
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
