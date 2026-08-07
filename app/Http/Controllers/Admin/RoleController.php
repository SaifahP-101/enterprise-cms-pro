<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    /**
     * แสดงรายการบทความ/สิทธิ์ทั้งหมด (Anti N+1 Query)
     */
    public function index()
    {
        $roles = Role::with('permissions')->withCount('users')->get();
        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        // จัดกลุ่ม Permissions ตาม Module เพื่อให้แสดงผลในหน้า Form แบบ Checkbox Group ง่ายๆ
        $permissionsByModule = Permission::all()->groupBy('module');
        return view('admin.roles.create', compact('permissionsByModule'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255|unique:roles,name',
            'permissions'   => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ], [
            'name.required' => 'กรุณาระบุชื่อบทบาทการใช้งาน',
            'name.unique'   => 'ชื่อบทบาทนี้มีในระบบแล้ว',
        ]);

        $role = Role::create([
            'name'        => $request->name,
            'slug'        => Str::slug($request->name, '_'),
            'description' => $request->description,
        ]);

        if (!empty($request->permissions)) {
            $role->permissions()->sync($request->permissions);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', 'บันทึกสร้างบทบาทและสิทธิ์การใช้งานสำเร็จแล้ว');
    }

    public function edit($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        $permissionsByModule = Permission::all()->groupBy('module');
        $attachedPermissionIds = $role->permissions->pluck('id')->toArray();

        return view('admin.roles.edit', compact('role', 'permissionsByModule', 'attachedPermissionIds'));
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name'          => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions'   => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update([
            'name'        => $request->name,
            'slug'        => Str::slug($request->name, '_'),
            'description' => $request->description,
        ]);

        $role->permissions()->sync($request->permissions ?? []);

        return redirect()->route('admin.roles.edit', $role->id)
            ->with('success', 'แก้ไขบทบาทและปรับปรุงสิทธิ์การใช้งานสำเร็จแล้ว');
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        if ($role->slug === 'super_admin') {
            return redirect()->back()->with('error', '🚨 ไม่สามารถลบบทบาท Super Admin หลักของระบบได้');
        }

        $role->delete();
        return redirect()->route('admin.roles.index')->with('success', 'ลบบทบาทผู้ใช้งานเรียบร้อยแล้ว');
    }
}