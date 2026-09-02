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
    
    protected $commands = [
        // ลงทะเบียน Command
        \App\Console\Commands\BackupOffsiteCommand::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        // ⏰ ทดสอบสั่งรันตอน 11:00 น.
        $schedule->command('cms:backup-offsite')
            ->dailyAt('11:00') // ⚡ เปลี่ยนเวลาตรงนี้
            ->withoutOverlapping()
            ->onOneServer() // ตอนนี้ใช้ได้แล้วเพราะเราแก้ตาราง cache แล้ว
            ->timezone('Asia/Bangkok') // บังคับ Timezone ป้องกันเซิร์ฟเวอร์เวลาเพี้ยน
            ->appendOutputTo(storage_path('logs/backup-schedule.log'));
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