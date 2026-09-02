<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessOffsiteBackup;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;

class BackupController extends Controller
{
    /**
     * แสดงหน้าควบคุมระบบ Backup และประวัติ Audit Logs
     */
    public function index(): View
    {
        $backupLogs = AuditLog::where('action', 'LIKE', 'OFFSITE_BACKUP_%')
            ->latest('id')
            ->paginate(10);

        return view('admin.settings.backup', compact('backupLogs'));
    }

    /**
     * สั่งรัน Manual Backup ด่วนจากหน้าเว็บ
     */
    public function runManualBackup(Request $request): JsonResponse
    {
        ProcessOffsiteBackup::dispatch(Auth::id());

        return response()->json([
            'success' => true,
            'message' => '🚀 ส่งคำสั่งสำรองข้อมูลเรียบร้อยแล้ว! ระบบกำลังประมวลผลเบื้องหลัง (Background Queue)',
        ]);
    }

    public function checkStatus()
    {
        // ดึงสถานะปัจจุบันจาก Cache ถ้าไม่มีแปลว่าว่างงาน (Idle)
        $status = Cache::get('offsite_backup_status', [
            'is_running' => false,
            'status'     => 'IDLE',
            'message'    => 'รอคำสั่ง...',
            'details'    => ''
        ]);

        return response()->json($status);
    }
}