<?php

namespace App\Jobs;

use App\Models\AuditLog;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessOffsiteBackup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;
    public int $tries = 1;

    protected ?int $triggeredByUserId;

    public function __construct(?int $userId = null)
    {
        $this->triggeredByUserId = $userId;
    }

    public function handle(): void
    {
        Log::info('📦 [Offsite Backup Engine] เริ่มต้นกระบวนการสำรองข้อมูลขึ้น Google Drive (Flat Upload Strategy)...');

        try {
            // 🛡️ 1. สำรองข้อมูลฐานข้อมูล MySQL (.sql.gz) ยิงตรงเข้า Root Folder
            $dbStatus = $this->backupDatabase();

            // 🛡️ 2. สำรองข้อมูลไฟล์สื่อแบบ Smart Incremental (แปลงชื่อไฟล์ป้องกัน Subfolder Bug)
            $mediaStatus = $this->syncMediaFiles();

            $summary = sprintf(
                "สำรองข้อมูลสำเร็จ | DB Status: %s | Media Sync: Uploaded %d files, Skipped %d files",
                $dbStatus,
                $mediaStatus['uploaded'],
                $mediaStatus['skipped']
            );

            Log::info("✅ [Offsite Backup Engine] {$summary}");
            $this->recordAuditLog('SUCCESS', $summary);

        } catch (Exception $e) {
            $errorMessage = 'การสำรองข้อมูลล้มเหลว: ' . $e->getMessage();
            Log::error("🚨 [Offsite Backup Engine Error] {$errorMessage}");

            $this->recordAuditLog('FAILED', $errorMessage);
            throw $e;
        }
    }

    /**
     * 🗄️ PART 1: สำรองข้อมูล Database (.sql.gz)
     */
    protected function backupDatabase(): string
    {
        $todayDate = date('Y-m-d');
        $fileName = "db_backup_{$todayDate}.sql.gz";
        
        // ⚡ อัปโหลดตรงเข้า Root Folder (ไม่ใส่คำว่า databases/ นำหน้า เพื่อหลีกเลี่ยง 404 Bug)
        $drivePath = $fileName; 
        
        $localTempDir = storage_path('app/backups');
        $localTempPath = "{$localTempDir}/{$fileName}";
        $googleDisk = Storage::disk('google');

        try {
            if ($googleDisk->exists($drivePath)) {
                Log::info("⏩ [DB Backup Skipped] พบไฟล์ {$drivePath} บน Google Drive แล้ว");
                return 'SKIPPED (Already Exists)';
            }
        } catch (Exception $e) {
            // Ignore error and proceed to upload
        }

        if (!file_exists($localTempDir)) {
            mkdir($localTempDir, 0755, true);
        }

        Log::info("⚙️ [DB Backup] กำลังดัมพ์ฐานข้อมูลด้วย mysqldump: {$fileName}...");

        $dbHost = config('database.connections.mysql.host');
        $dbPort = config('database.connections.mysql.port', '3306');
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');

        // ใช้ --no-tablespaces เพื่อป้องกันปัญหาสิทธิ์ PROCESS
        $command = sprintf(
            'mysqldump --single-transaction --quick --no-tablespaces --host=%s --port=%s --user=%s --password=%s %s | gzip > %s',
            escapeshellarg($dbHost),
            escapeshellarg($dbPort),
            escapeshellarg($dbUser),
            escapeshellarg($dbPass),
            escapeshellarg($dbName),
            escapeshellarg($localTempPath)
        );

        $returnVar = null;
        $output = [];
        exec($command, $output, $returnVar);

        if ($returnVar !== 0 || !file_exists($localTempPath) || filesize($localTempPath) === 0) {
            throw new Exception("คำสั่ง mysqldump ทำงานล้มเหลว (Exit Code: {$returnVar})");
        }

        Log::info("☁️ [DB Backup] กำลัง Stream Upload {$fileName} ขึ้น Google Drive...");
        $fileStream = fopen($localTempPath, 'r+');
        $googleDisk->put($drivePath, $fileStream);
        
        if (is_resource($fileStream)) {
            fclose($fileStream);
        }

        if (file_exists($localTempPath)) {
            unlink($localTempPath);
        }

        return 'UPLOADED_SUCCESSFULLY';
    }

    /**
     * 📁 PART 2: Smart Incremental Media Sync
     */
    protected function syncMediaFiles(): array
    {
        Log::info('🔍 [Media Sync] เริ่มสแกนไฟล์ใน storage/app/public/...');

        $localPublicDisk = Storage::disk('public');
        $googleDisk = Storage::disk('google');
        $allLocalFiles = $localPublicDisk->allFiles();

        $uploadedCount = 0;
        $skippedCount = 0;

        foreach ($allLocalFiles as $relativePath) {
            if (strpos(basename($relativePath), '.') === 0) {
                continue;
            }

            // ⚡ แปลงชื่อไฟล์และไดเรกทอรีให้แบนราบ (Flat Name) เช่น images/cover.jpg -> media_images_cover.jpg
            $safeName = str_replace('/', '_', $relativePath);
            $driveTargetPath = "media_{$safeName}";

            try {
                if ($googleDisk->exists($driveTargetPath)) {
                    $skippedCount++;
                    continue;
                }
            } catch (Exception $e) {
                // Ignore exists failure and upload
            }

            $fileStream = $localPublicDisk->readStream($relativePath);
            if ($fileStream) {
                $googleDisk->put($driveTargetPath, $fileStream);
                
                if (is_resource($fileStream)) {
                    fclose($fileStream);
                }
                
                $uploadedCount++;
                Log::info("⬆️ [Media Sync Uploaded] {$driveTargetPath}");
            }
        }

        return [
            'uploaded' => $uploadedCount,
            'skipped'  => $skippedCount,
        ];
    }

    protected function recordAuditLog(string $status, string $details): void
    {
        try {
            AuditLog::create([
                'user_id'    => $this->triggeredByUserId,
                'model_type' => self::class,
                'model_id'   => 0,
                'action'     => 'OFFSITE_BACKUP_' . $status,
                'old_values' => null,
                'new_values' => json_encode([
                    'trigger_type' => $this->triggeredByUserId ? 'MANUAL' : 'CRON_SCHEDULED',
                    'details'      => $details,
                ], JSON_UNESCAPED_UNICODE),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'Laravel CLI / Queue Worker',
            ]);
        } catch (Exception $e) {
            Log::warning('ไม่สามารถบันทึก AuditLog แบ็กอัปได้: ' . $e->getMessage());
        }
    }
}