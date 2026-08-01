<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * แสดงรายชื่อสมาชิกทั้งหมดพร้อมระบบแบ่งหน้า (Pagination)
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // ใช้ Pagination เพื่อจำกัดการดึงข้อมูลและลดภาระของหน่วยความจำในระบบ
        $users = User::orderBy('id', 'desc')->paginate(10);
        
        return view('admin.users.index', compact('users'));
    }

    /**
     * บันทึกข้อมูลสมาชิกใหม่เข้าสู่ระบบ
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'is_admin' => 'nullable|boolean',
        ]);

        // เข้ารหัสผ่านด้วยกลไก Bcrypt ที่ปลอดภัยสูงก่อนบันทึก
        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_admin' => $request->has('is_admin'),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "เพิ่มสมาชิก บัญชี {$user->email} เข้าสู่ระบบเรียบร้อยแล้ว");
    }

    /**
     * อัปเดตข้อมูลสมาชิก (รองรับการข้ามการเปลี่ยนรหัสผ่านหากปล่อยว่าง)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'is_admin' => 'nullable|boolean',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        
        // ตรวจสอบสิทธิ์การเป็น Admin (แปลงค่าจาก Checkbox ให้เป็น Boolean)
        $user->is_admin = $request->has('is_admin');

        // ตรรกะการเปลี่ยนรหัสผ่าน: จะเปลี่ยนเฉพาะเมื่อมีการป้อนข้อมูลเข้ามาใหม่เท่านั้น
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        // ระบบจะคำนวณหาค่าต่าง (Diffing) และบันทึกลง Audit Logs อัตโนมัติเมื่อสั่ง save()
        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', "อัปเดตข้อมูลของสมาชิก {$user->name} เรียบร้อยแล้ว");
    }

    /**
     * ลบสมาชิกออกจากระบบ
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // 🛡️ ป้องกันความปลอดภัย: ห้ามแอดมินลบตัวเองออกจากระบบเด็ดขาด
        if (auth()->id() === $user->id) {
            return redirect()->back()->with('error', 'คุณไม่สามารถลบบัญชีของตัวเองที่กำลังใช้งานอยู่ได้');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'ลบบัญชีผู้ใช้งานออกจากระบบเรียบร้อยแล้ว');
    }
}