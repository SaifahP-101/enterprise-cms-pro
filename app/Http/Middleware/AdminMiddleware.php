<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // 🛡️ RBAC Gate Protection: ต้องเข้าเงื่อนไขว่าล็อกอินแล้ว และคีย์ฟิลด์ is_admin ในตาราง Users ต้องเป็น true
        if (Auth::check() && Auth::user()->is_admin) {
            return $next($request);
        }

        // หากไม่มีสิทธิ์เข้าถึง ให้ดีดกลับหน้าแรกพร้อมสลักข้อความแจ้งเตือนความปลอดภัย
        return redirect('/')->with('error', 'สิทธิ์การเข้าถึงพื้นที่ระบบควบคุมหลังบ้านองค์กรถูกปฏิเสธ');
    }
}