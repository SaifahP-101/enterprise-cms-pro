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

        // 2. 🛡️ Advanced Security: สร้าง Throttle Key ป้องกันการเดารหัสผ่านรัวๆ (จำกัด 5 ครั้งต่อนาที)
        $throttleKey = Str::lower($request->input('email')) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => [__('auth.throttle', ['seconds' => $seconds])],
            ]);
        }

        // 3. ทำการตรวจสอบสิทธิ์ในฐานข้อมูล
        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember');

        if (Auth::attempt($credentials, $remember)) {
            // 4. ⚡ Security Best Practice: ล้างประวัติการจำกัดจำนวนการเดารหัส
            RateLimiter::clear($throttleKey);

            // 5. 🛡️ Session Fixation Protection: รีเซ็ต ID ของ Session ใหม่ทั้งหมด ป้องกันการขโมย Cookie สิทธิ์
            $request->session()->regenerate();

            // 6. ตรรกะแยกแยะทางเข้า: หากเป็นแอดมินให้ยิงไปหน้าแดชบอร์ดหลังบ้าน หากไม่ใช่ให้ไปหน้าแรก
            if (Auth::user()->is_admin) {
                return redirect()->intended('admin/menus');
            }
            return redirect()->intended('/');
        }

        // 7. บันทึกประวัติการล็อกอินพลาดลงระบบนับถอยหลัง
        RateLimiter::hit($throttleKey, 60);

        throw ValidationException::withMessages([
            'email' => [__('auth.failed')],
        ]);
    }

    /**
     * ทำลายสิทธิ์การล็อกอินและเคลียร์หน่วยความจำชั่วคราว
     */
    public function logout(Request $request)
    {
        Auth::logout();

        // เคลียร์และยกเลิกโทเคน Session เดิม
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'ลงชื่อออกจากระบบสำเร็จ');
    }
}