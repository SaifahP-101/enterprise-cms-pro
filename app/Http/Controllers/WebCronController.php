<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class WebCronController extends Controller
{
    // 🛡️ ควรกำหนดใน .env เช่น env('WEBCRON_TOKEN') เพื่อความปลอดภัยสูงสุด
    private $secretToken = 'L6IU7nSS2mzeeqVRAeNJ56lYiZxHuucK!';

    /**
     * รับคำสั่งจาก cron-job.org เพื่อสั่งรัน Schedule หรือ Queue Worker
     */
    public function handle(Request $request, $action)
    {
        // 🛡️ 1. ตรวจสอบความปลอดภัย (Token Verification)
        if ($request->query('token') !== $this->secretToken) {
            // Log::warning('Unauthorized Web Cron attempt', ['ip' => $request->ip()]);
            // // สับขาหลอกเป็น 404 ป้องกัน Hacking Bots สแกนหาช่องโหว่
            // abort(404, 'Not Found'); 
        }

        // ⚡ 2. ท่าไม้ตายสำหรับ Shared Hosting (Bypass Server Limits)
        ignore_user_abort(true);          // ปล่อยให้ทำงานเบื้องหลังต่อแม้เบราว์เซอร์จะตัดการเชื่อมต่อ
        set_time_limit(0);                // ปลดล็อก Execution Time (รัน Backup 4GB ได้จนจบ)
        ini_set('memory_limit', '1024M'); // ขยาย RAM ชั่วคราวป้องกัน Memory Exhausted

        try {
            // ⚙️ Action 1: รัน Scheduler ทั่วไป
            if ($action === 'schedule') {
                Log::info('🤖 [Web Cron] Schedule กำลังรัน Schedule...');
                Artisan::call('schedule:run');
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'Scheduler executed successfully.',
                    'output' => Artisan::output()
                ]);
            }

            // ⚙️ Action 2: รัน Queue Worker (สำหรับ Backup)
            if ($action === 'queue') {
                Log::info('🤖 [Web Cron] Queue Worker กำลังรัน...');
                Artisan::call('queue:work', [
                    '--stop-when-empty' => true, // หยุดทันทีเมื่อไม่มีคิวเหลือ (ป้องกันโฮสต์แบน)
                    '--timeout' => 7200,         // ขยายเวลา Timeout เป็น 2 ชั่วโมงให้ตรงกับ Job
                    '--tries' => 1
                ]);
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'Queue processed successfully.',
                    'output' => Artisan::output()
                ]);
            }

            return abort(404, 'Action not found');

        } catch (\Exception $e) {
            // บันทึก Error จริงลง Log แต่พ่น HTTP 500 กลับไปแบบปิดบังข้อมูล (Security)
            Log::error('🚨 Web Cron Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error', 
                'message' => 'Internal Server Error'
            ], 500);
        }
    }
}