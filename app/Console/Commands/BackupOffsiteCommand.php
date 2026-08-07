<?php

namespace App\Console\Commands;

use App\Jobs\ProcessOffsiteBackup;
use Exception;
use Illuminate\Console\Command;

class BackupOffsiteCommand extends Command
{
    protected $signature = 'cms:backup-offsite {--sync : รันกระบวนการทันทีโดยไม่ผ่าน Background Queue}';
    protected $description = 'สั่งสำรองข้อมูลฐานข้อมูล (.sql.gz) และไฟล์สื่อขึ้น Google Drive (Smart Offsite Backup)';

    public function handle(): int
    {
        $this->info('🚀 [CMS Offsite Backup] เริ่มต้นสั่งงานระบบสำรองข้อมูล...');

        try {
            if ($this->option('sync')) {
                $this->warn('⚡ คุณกำลังรันในโหมด Synchronous...');
                dispatch_sync(new ProcessOffsiteBackup(null));
                $this->info('✅ [CMS Offsite Backup] สำรองข้อมูลแบบ Synchronous สำเร็จสมบูรณ์!');
            } else {
                ProcessOffsiteBackup::dispatch(null);
                $this->info('📥 [CMS Offsite Backup] ส่ง Job เข้าสู่ระบบ Queue เรียบร้อยแล้ว');
            }

            return 0;

        } catch (Exception $e) {
            $this->error('🚨 [CMS Offsite Backup Error] ข้อผิดพลาด: ' . $e->getMessage());
            return 1;
        }
    }
}