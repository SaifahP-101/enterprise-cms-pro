<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * หน้าบอร์ดควบคุมแสดงรายการโครงสร้างเมนูหลัก
     */
    public function index()
    {
        // ⚡ Anti N+1 Optimization: ดึงเฉพาะรากฐานเมนูหลัก (parent_id เป็น null) พ่วง eager load กิ่งเมนูย่อยชั้นใน
        $menus = Menu::with('children')
                     ->whereNull('parent_id')
                     ->orderBy('sort_order', 'asc')
                     ->get();

        // ดึงรายชื่อเมนูรากฐานทั้งหมดส่งไปสแตนด์บายในกล่อง Dropdown สำหรับให้เลือกผูกกิ่งย่อย
        $parentMenus = Menu::whereNull('parent_id')->orderBy('sort_order', 'asc')->get();

        return view('admin.menus.index', compact('menus', 'parentMenus'));
    }

    /**
     * จัดเก็บข้อมูลเมนูชิ้นใหม่เข้าสู่สารระบบ
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'     => 'required|string|max:150',
            'url'       => 'nullable|string|max:255',
            'parent_id' => 'nullable|integer|exists:menus,id',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->has('is_active');

        // ตรรกะคำนวณลำดับต่อท้ายอัตโนมัติหากปล่อยฟิลด์ว่างไว้
        if (empty($data['sort_order'])) {
            $data['sort_order'] = Menu::where('parent_id', $request->parent_id)->max('sort_order') + 1;
        }

        Menu::create($data);

        return redirect()->route('admin.menus.index')->with('success', 'จัดเก็บโครงสร้างเมนูใหม่เข้าสารระบบสำเร็จ');
    }

    /**
     * อัปเดตข้อมูลรายละเอียดโครงสร้างเมนูเดิม
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title'     => 'required|string|max:150',
            'url'       => 'nullable|string|max:255',
            'parent_id' => 'nullable|integer|exists:menus,id|not_in:' . $id, // 🛡️ ป้องกันระบบวนลูปเอารูปตัวเองผูกตัวเอง
        ]);

        $menu = Menu::findOrFail($id);
        $data = $request->all();
        $data['is_active'] = $request->has('is_active');

        $menu->update($data);

        return redirect()->route('admin.menus.index')->with('success', 'ปรับปรุงมิติข้อมูลเมนูเสร็จสิ้น');
    }

    /**
     * ทำลายเรคคอร์ดเมนูถาวร (พร้อมล้างกิ่งย่อยออโต้ด้วย Cascade Constraints)
     */
    public function destroy($id)
    {
        $menu = Menu::findOrFail($id);
        $menu->delete();

        return redirect()->route('admin.menus.index')->with('success', 'ถอนรากถอนโคนรายการเมนูออกจากฐานข้อมูลสำเร็จ');
    }
}