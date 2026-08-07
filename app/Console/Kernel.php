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
        // ⏰ สั่งรัน Offsite Backup ทุกวันในเวลา 02:00 น.
        // - withoutOverlapping(): ป้องกันการรันซ้อนหากงานเก่ายังไม่เสร็จ
        // - onOneServer(): รันเฉพาะบน Server เครื่องเดียวในกรณีทำ Multi-server Load Balancer
        $schedule->command('cms:backup-offsite')
            ->dailyAt('02:00')
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