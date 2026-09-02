<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebCronSecurity
{
    public function handle(Request $request, Closure $next)
    {
        // 1. กำหนด IP ที่อนุญาตให้รัน Cron ได้ (เช่น IP ของ Server ตัวเอง หรือ IP ของ cron-job.org)
        $allowedIps = [
            '127.0.0.1', 
            '172.69.40.186',
            '172.69.40.188',
            '172.69.40.189'
        ];

        $clientIp = trim($request->ip()); 

        // 2. กำหนด Token ลับที่ต้องส่งมาพร้อม URL (เช่น ?token=SecretCron2026!)
        $validToken = 'L6IU7nSS2mzeeqVRAeNJ56lYiZxHuucK!';
        $rawToken = $request->query('token');
        $clientToken = urldecode($rawToken);

        // 3. ตรวจสอบเงื่อนไข (ต้องตรงทั้ง IP และ Token)
        if (!in_array($clientIp, $allowedIps) || $clientToken !== $validToken) {
            // ⚡ อัปเกรดให้แสดง URL และ Token ที่มันพยายามยิงเข้ามา
            Log::info("Blocked Cron Attempt from IP: {$clientIp} | URL: " . $request->fullUrl(). "| Token: $clientToken| Valid Token: $validToken");
            // abort(404);
        }

        return $next($request);
    }
}