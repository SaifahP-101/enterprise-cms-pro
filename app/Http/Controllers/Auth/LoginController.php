<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        // หากล็อกอินอยู่แล้ว ให้แยกทางเข้าอัตโนมัติ
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->is_admin || $user->roles()->exists()) {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('home');
        }

        return view('auth.login');
    }

    /**
     * ประมวลผลการยืนยันตัวตนเข้าใช้งานระบบ
     */
    public function login(Request $request)
    {
        // 1. ตรวจสอบความถูกต้องของข้อมูลอินพุต
        $request->validate([
            'email'    => 'required|email|string',
            'password' => 'required|string',
        ]);

        // 2. 🛡️ Advanced Security: ป้องกันการเดารหัสผ่านรัวๆ (Throttle 5 ครั้งต่อนาที)
        $throttleKey = Str::lower($request->input('email')) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => [__('auth.throttle', ['seconds' => $seconds])],
            ]);
        }

        // 3. ตรวจสอบสิทธิ์ในฐานข้อมูล
        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember');

        if (Auth::attempt($credentials, $remember)) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            $user = Auth::user();

            // ⚡ 4. ตรรกะนำทางบุคลากรเข้าสู่หลังบ้าน (Enterprise Routing Logic)
            if ($user->is_admin || $user->roles()->exists()) {
                
                // 🎯 ป้องกันกับดัก intended() ค้างอยู่ที่หน้าแรก (/)
                $intendedUrl = session()->get('url.intended');
                if (!$intendedUrl || $intendedUrl === url('/')) {
                    session()->forget('url.intended');
                    return redirect()->route('admin.dashboard');
                }

                return redirect()->intended(route('admin.dashboard'));
            }

            // ผู้ใช้งานทั่วไปที่ไม่มี Role ใดๆ ให้ไปหน้าบ้านปกติ
            return redirect()->intended(route('home'));
        }

        RateLimiter::hit($throttleKey, 60);

        throw ValidationException::withMessages([
            'email' => [__('auth.failed')],
        ]);
    }

    /**
     * ทำลายเซสชันล็อกเอาท์
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'ลงชื่อออกจากระบบสำเร็จแล้ว');
    }
}