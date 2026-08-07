<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * แสดงรายชื่อสมาชิกทั้งหมดพร้อมสิทธิ์ บทบาท และขอบเขตหมวดหมู่ที่ดูแล
     */
    public function index()
    {
        $users = User::with(['roles', 'categories'])
            ->orderBy('id', 'desc')
            ->paginate(15);

        $roles = Role::orderBy('name', 'asc')->get();
        $categories = Category::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        return view('admin.users.index', compact('users', 'roles', 'categories'));
    }

    /**
     * บันทึกข้อมูลสมาชิกใหม่เข้าสู่ระบบ
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|string|email|max:255|unique:users,email',
            'password'     => 'required|string|min:8|confirmed',
            'is_admin'     => 'required|in:0,1',
            'roles'        => 'nullable|array',
            'roles.*'      => 'integer|exists:roles,id',
            'categories'   => 'nullable|array',
            'categories.*' => 'integer|exists:categories,id',
        ], [
            'name.required'     => 'กรุณาระบุชื่อ-นามสกุลบุคลากร',
            'email.required'    => 'กรุณาระบุอีเมลองค์กร',
            'email.unique'      => 'อีเมลนี้ถูกใช้งานในระบบแล้ว',
            'password.required' => 'กรุณากำหนดรหัสผ่านเริ่มต้น',
            'password.min'      => 'รหัสผ่านต้องมีความยาวอย่างน้อย 8 ตัวอักษร',
            'password.confirmed'=> 'รหัสผ่านยืนยันไม่ตรงกัน',
        ]);

        // 🎯 กำหนดค่า is_admin แม่นยำตาม Select Value (0 หรือ 1)
        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_admin' => $request->input('is_admin') == '1',
        ]);

        if (!empty($request->roles)) {
            $user->roles()->sync($request->roles);
        }

        if (!empty($request->categories)) {
            $user->categories()->sync($request->categories);
        }

        return redirect()->route('admin.users.index')
            ->with('success', "เพิ่มบัญชีบุคลากร {$user->email} เข้าสู่ระบบเรียบร้อยแล้ว");
    }

    /**
     * อัปเดตข้อมูลสมาชิกและระดับสิทธิ์แอดมินหลัก
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password'     => 'nullable|string|min:8|confirmed',
            'is_admin'     => 'required|in:0,1',
            'roles'        => 'nullable|array',
            'roles.*'      => 'integer|exists:roles,id',
            'categories'   => 'nullable|array',
            'categories.*' => 'integer|exists:categories,id',
        ], [
            'name.required'      => 'กรุณาระบุชื่อ-นามสกุลบุคลากร',
            'email.required'     => 'กรุณาระบุอีเมลองค์กร',
            'email.unique'       => 'อีเมลนี้ถูกใช้งานในระบบแล้ว',
            'password.min'       => 'รหัสผ่านใหม่ต้องมีความยาวอย่างน้อย 8 ตัวอักษร',
            'password.confirmed' => 'รหัสผ่านยืนยันไม่ตรงกัน',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        
        // 🎯 แก้ไขจุดนี้: อ่านค่า Boolean จาก Select Value ตรงๆ (1 = true, 0 = false)
        $user->is_admin = $request->input('is_admin') == '1';

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        $user->roles()->sync($request->roles ?? []);
        $user->categories()->sync($request->categories ?? []);

        return redirect()->route('admin.users.index')
            ->with('success', "อัปเดตข้อมูลและสิทธิ์การใช้งานของ {$user->name} เรียบร้อยแล้ว");
    }

    /**
     * ลบบัญชีสมาชิกออกจากระบบ
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if (Auth::id() === $user->id) {
            return redirect()->back()->with('error', '🚨 คุณไม่สามารถลบบัญชีของตัวเองที่กำลังใช้งานอยู่ได้');
        }

        $user->roles()->detach();
        $user->categories()->detach();
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'ลบบัญชีผู้ใช้งานออกจากระบบเรียบร้อยแล้ว');
    }
}