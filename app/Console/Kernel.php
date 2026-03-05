<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\ApiCron::class,
        Commands\SendStudentEmails::class,
        Commands\SendSummaryEmails::class,
        Commands\VerifyPendingPayment::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('api:day')->dailyAt('18:15');
        // $schedule->command('emails:send-student')->everyTenMinutes();
        // $schedule->command('emails:send-admin')->dailyAt('18:15');
        // $schedule->command('payment:verify-status')->dailyAt('18:15');
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
