<?php

namespace App\Jobs;

use App\Models\AuditLog;
use App\Models\BackupSyncedFile;
use Exception;
use Ifsnop\Mysqldump\Mysqldump;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ProcessOffsiteBackup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // ขยายเวลาให้ Job ทำงานได้สูงสุด 2 ชั่วโมง ป้องกัน Timeout
    public int $timeout = 7200; 
    public int $tries = 1;

    protected ?int $triggeredByUserId;

    public function __construct(?int $userId = null)
    {
        $this->triggeredByUserId = $userId;
    }

    public function handle(): void
    {
        Log::info('📦 [Offsite Backup Engine] เริ่มกระบวนการสำรองข้อมูล (Directory-based Zipped Differential)...');
        $this->updateProgress('กำลังเตรียมการสำรองข้อมูล...', 'ระบบกำลังจัดเตรียม Temp Folder และเชื่อมต่อ Google Drive');

        $timestamp = date('Y-m-d_H-i-s');
        $tempDir = storage_path('app/backups');
        
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $sqlFileName = "db_{$timestamp}.sql";
        $sqlLocalPath = "{$tempDir}/{$sqlFileName}";

        try {
            // Step 1 & 2: จัดการฐานข้อมูล
            $this->dumpDatabase($sqlLocalPath);
            $this->uploadDatabase($sqlLocalPath, $sqlFileName);

            // Step 3: ประมวลผล Media แบบแยกก้อนตามโฟลเดอร์
            $syncResult = $this->syncMediaFiles($tempDir, $timestamp);

            // Step 4: คืนพื้นที่ดิสก์ Local
            @unlink($sqlLocalPath);

            $summary = "สำรองข้อมูลสำเร็จ | ดัมพ์ Database เรียบร้อย | Media: อัปโหลด Zip ใหม่ {$syncResult['uploaded']} ไฟล์ (ข้าม {$syncResult['skipped']} ไฟล์)";
            Log::info("✅ [Offsite Backup Engine] {$summary}");
            $this->recordAuditLog('SUCCESS', $summary);

            $this->updateProgress('สำรองข้อมูลเสร็จสมบูรณ์ 🎉', $summary, false, 'SUCCESS');

        } catch (Exception $e) {
            $errorMessage = 'การสำรองข้อมูลล้มเหลว: ' . $e->getMessage();
            Log::error("🚨 [Offsite Backup Engine Error] {$errorMessage}");
            
            @unlink($sqlLocalPath);
            $this->recordAuditLog('FAILED', $errorMessage);
            $this->updateProgress('เกิดข้อผิดพลาดระหว่างกระบวนการ!', $errorMessage, false, 'FAILED');

            throw $e;
        }
    }

    /**
     * ดัมพ์ฐานข้อมูลด้วย Pure PHP (ifsnop/mysqldump-php)
     */
    protected function dumpDatabase(string $localTempPath): void
    {
        $this->updateProgress('กำลังดัมพ์ฐานข้อมูล (Step 1/3)', 'กำลังแปลงข้อมูลเป็นไฟล์ SQL...');
        Log::info("⚙️ [Step 1] กำลังดัมพ์ฐานข้อมูล...");

        $dbHost = config('database.connections.mysql.host');
        $dbPort = config('database.connections.mysql.port', '3306');
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');

        try {
            $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName}";
            $dumpSettings = ['add-drop-table' => true, 'skip-definer' => true, 'skip-triggers' => false];
            $dump = new Mysqldump($dsn, $dbUser, $dbPass, $dumpSettings);
            $dump->start($localTempPath);

            if (!file_exists($localTempPath) || filesize($localTempPath) === 0) {
                throw new Exception("ไฟล์ SQL ที่ดัมพ์ออกมามีขนาด 0 Byte");
            }
        } catch (Exception $e) {
            throw new Exception("ดัมพ์ฐานข้อมูลล้มเหลว: " . $e->getMessage());
        }
    }

    /**
     * อัปโหลดไฟล์ Database แบบ Streaming
     */
    protected function uploadDatabase(string $localPath, string $fileName): void
    {
        $this->updateProgress('กำลังอัปโหลดฐานข้อมูล (Step 2/3)', "กำลัง Streaming ไฟล์ {$fileName}...");
        Log::info("☁️ [Step 2] กำลังอัปโหลดไฟล์ Database: {$fileName}...");
        
        $googleDisk = Storage::disk('google');
        $fileStream = fopen($localPath, 'r+');
        
        if (!$fileStream) {
            throw new Exception("ไม่สามารถเปิด Stream เพื่ออ่านไฟล์ SQL ได้");
        }

        $googleDisk->put($fileName, $fileStream);
        
        if (is_resource($fileStream)) {
            fclose($fileStream);
        }
    }

    /**
     * ⚡ Directory-based Zipped Differential Media Sync
     * สร้างไฟล์ Zip 1 ก้อน ต่อ 1 โฟลเดอร์ (ลดปัญหาไฟล์ใหญ่เกินไปอย่างเป็นธรรมชาติ)
     */
    protected function syncMediaFiles(string $tempDir, string $timestamp): array
    {
        $this->updateProgress('กำลังเปรียบเทียบไฟล์ Media (Step 3/3)', 'กำลังสแกนและแยกก้อน Zip ตามโฟลเดอร์...');
        Log::info("🗂️ [Step 3] เริ่มกระบวนการ Zipped Media Sync (แบ่งก้อนตามโฟลเดอร์)...");

        $publicDisk = Storage::disk('public');
        $googleDisk = Storage::disk('google');
        
        $targetDirectories = [
            'contents/covers', 
            'contents/galleries', 
            'contents/inline_images',
            'pages/inline_images', 
            'popups', 
            'slideshows'
        ];

        $totalSkippedCount = 0;
        $totalUploadedCount = 0;

        // วนลูปทำงานทีละโฟลเดอร์
        foreach ($targetDirectories as $directory) {
            $files = $publicDisk->allFiles($directory);
            $filesToUpdateInDb = [];
            
            // แปลงชื่อโฟลเดอร์ให้ปลอดภัยสำหรับตั้งเป็นชื่อไฟล์ (เช่น contents/covers -> contents_covers)
            $safeDirName = str_replace('/', '_', $directory);
            $zipFileName = "media_diff_{$timestamp}_{$safeDirName}.zip";
            $zipFilePath = "{$tempDir}/{$zipFileName}";
            
            $zip = new ZipArchive();
            $isZipOpened = false; // ตัวแปรเช็กว่ามีการสร้างไฟล์ Zip สำหรับโฟลเดอร์นี้หรือยัง

            foreach ($files as $relativePath) {
                if (strpos(basename($relativePath), '.') === 0) continue; 

                $absolutePath = $publicDisk->path($relativePath);
                $currentFileHash = md5_file($absolutePath);
                $syncedRecord = BackupSyncedFile::where('file_path', $relativePath)->first();

                // ข้ามไฟล์ที่ซ้ำ
                if ($syncedRecord && $syncedRecord->file_hash === $currentFileHash) {
                    $totalSkippedCount++;
                    continue;
                }

                // หากเป็นไฟล์ใหม่ และยังไม่ได้เปิด Zip ให้สร้างไฟล์ Zip ขึ้นมา
                if (!$isZipOpened) {
                    if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                        throw new Exception("ไม่สามารถสร้างไฟล์ Zip: {$zipFilePath}");
                    }
                    $isZipOpened = true;
                }

                $zip->addFile($absolutePath, $relativePath);
                $filesToUpdateInDb[] = [
                    'file_path' => $relativePath,
                    'file_hash' => $currentFileHash
                ];
            }

            // เมื่อสแกนโฟลเดอร์จบ หากมีการนำไฟล์เข้า Zip ให้ทำการปิดไฟล์และอัปโหลด
            if ($isZipOpened) {
                $zip->close();
                
                $this->uploadZipDirectory($zipFilePath, $zipFileName, $googleDisk, $directory);
                $this->commitHashesToDatabase($filesToUpdateInDb);
                
                $totalUploadedCount += count($filesToUpdateInDb);
                @unlink($zipFilePath); // คืนพื้นที่ทันทีหลังอัปโหลดเสร็จ

                // 🛡️ หน่วงเวลา 2 วินาที ป้องกัน Google Drive API แบนจากการอัปโหลดไฟล์ติดๆ กัน
                sleep(2);
            }
        }

        return [
            'uploaded' => $totalUploadedCount,
            'skipped'  => $totalSkippedCount
        ];
    }

    /**
     * Helper: อัปโหลดไฟล์ Zip ประจำโฟลเดอร์
     */
    protected function uploadZipDirectory(string $zipFilePath, string $zipFileName, $googleDisk, string $directory): void
    {
        $this->updateProgress("กำลังอัปโหลดโฟลเดอร์ {$directory}", "กำลังอัปโหลดไฟล์ {$zipFileName}...");
        Log::info("📦 [Step 3] กำลังอัปโหลด Zip ประจำโฟลเดอร์ ({$directory}): {$zipFileName}...");

        $fileStream = fopen($zipFilePath, 'r+');
        if ($fileStream) {
            $googleDisk->put($zipFileName, $fileStream); 
            
            fclose($fileStream);
        } else {
            throw new Exception("ไม่สามารถเปิดอ่านไฟล์ Zip {$zipFileName} เพื่ออัปโหลดได้");
        }
    }

    /**
     * Helper: บันทึกประวัติ Hash ลงฐานข้อมูล
     */
    protected function commitHashesToDatabase(array $filesToUpdateInDb): void
    {
        foreach ($filesToUpdateInDb as $fileData) {
            BackupSyncedFile::updateOrCreate(
                ['file_path' => $fileData['file_path']],
                ['file_hash' => $fileData['file_hash']]
            );
        }
    }

    /**
     * บันทึก Audit Log
     */
    protected function recordAuditLog(string $status, string $details): void
    {
        try {
            AuditLog::create([
                'user_id'        => $this->triggeredByUserId,
                'auditable_type' => self::class, 
                'auditable_id'   => 0,           
                'action'         => 'OFFSITE_BACKUP_' . $status,
                'old_values'     => null,
                'new_values'     => [
                    'trigger_type' => $this->triggeredByUserId ? 'MANUAL' : 'CRON_SCHEDULED',
                    'details'      => $details,
                ],
                'ip_address'     => request()->ip() ?? '127.0.0.1',
                'user_agent'     => 'Laravel Worker Engine',
            ]);
        } catch (Exception $e) {
            Log::warning('ไม่สามารถบันทึก AuditLog แบ็กอัปได้: ' . $e->getMessage());
        }
    }

    /**
     * อัปเดตสถานะขึ้น Cache แบบ Real-time
     */
    protected function updateProgress(string $message, string $details = '', bool $isRunning = true, string $status = 'RUNNING'): void
    {
        Cache::put('offsite_backup_status', [
            'is_running' => $isRunning,
            'status'     => $status,
            'message'    => $message,
            'details'    => $details
        ], 7200);
    }

    /**
     * 🚨 ดักจับ Error ขั้นรุนแรง (เช่น Timeout หรือ Out of Memory)
     */
    public function failed(\Throwable $exception)
    {
        $errorMessage = 'Queue Worker ล้มเหลวฉุกเฉิน: ' . $exception->getMessage();
        Log::emergency("💀 [Offsite Backup Engine DEAD] {$errorMessage}");
        
        $this->updateProgress('ระบบหยุดทำงานกะทันหัน!', $errorMessage, false, 'FAILED');
        $this->recordAuditLog('FATAL_ERROR', $errorMessage);
    }
}