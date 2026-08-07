<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * ดักจับสิทธิ์การเข้าถึงพื้นที่บริหารจัดการหลังบ้าน (/admin/*)
     */
    public function handle(Request $request, Closure $next)
    {
        // 1. ตรวจสอบว่าได้ล็อกอินเข้าสู่ระบบหรือยัง
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // 🛡️ 2. อนุญาตให้เข้าหลังบ้านได้ หากเป็น Super Admin (is_admin = 1) หรือ "มี Roles ผูกอยู่"
        if ($user->is_admin || $user->roles()->exists()) {
            return $next($request);
        }

        // 3. หากเป็น User ธรรมดาที่ไม่มี Role ใดๆ เลย ให้ปฏิเสธการเข้าถึง
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => '🚨 คุณไม่มีสิทธิ์เข้าถึงระบบบริหารจัดการหลังบ้าน'
            ], 403);
        }

        return redirect()->route('home')->with('error', '🚨 บัญชีของคุณไม่มีสิทธิ์เข้าถึงส่วนผู้ดูแลระบบ');
    }
}