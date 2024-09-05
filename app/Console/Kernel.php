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
        // TODO: Schedule the commands here

        /*
        if (config('app.env') == 'production') {
            $schedule->command('send:financereport')
                ->weeklyOn(0, '17:00'); //UTC time
            $schedule->command('job:deactivate-expired-users')
                ->dailyOn(0, '12:38'); //UTC time
        }
        */
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
