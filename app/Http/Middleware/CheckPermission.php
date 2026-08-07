<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    /**
     * ดักจับสิทธิ์การเข้าถึงผ่าน Parameter Permission Slug
     */
    public function handle(Request $request, Closure $next, string $permission)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // ตรวจสอบสิทธิ์ผ่าน Trait
        if (!$user->hasPermission($permission)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => '🚨 คุณไม่มีสิทธิ์เข้าถึงหรือดำเนินการในส่วนนี้'
                ], 403);
            }

            abort(403, '🚨 คุณไม่มีสิทธิ์เข้าถึงโมดูลการทำงานนี้');
        }

        return $next($request);
    }
}