<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        // ดึงหมวดหมู่ทั้งหมดเพื่อส่งไปประมวลผลบน Local DataTables
        $categories = Category::orderBy('sort_order', 'asc')->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'image_size'      => 'nullable|string|max:100',
            'image_dimension' => 'nullable|string|max:100',
            'image_type'      => 'nullable|string|max:100',
            'sort_order'      => 'required|integer|min:0',
        ]);

        // ระบบจะสร้าง Auto Thai Slug ในชั้นโมเดล และบันทึก Audit Logs ผ่าน Trait อัตโนมัติ
        Category::create([
            'name'            => $validated['name'],
            'image_size'      => $validated['image_size'] ?? '2MB',
            'image_dimension' => $validated['image_dimension'] ?? '1200 x 630 Pixels',
            'image_type'      => $validated['image_type'] ?? '.JPG .png',
            'sort_order'      => $validated['sort_order'],
            'is_active'       => $request->has('is_active'),
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'สร้างหมวดหมู่ศิลปวัฒนธรรมใหม่สำเร็จ');
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'image_size'      => 'nullable|string|max:100',
            'image_dimension' => 'nullable|string|max:100',
            'image_type'      => 'nullable|string|max:100',
            'sort_order'      => 'required|integer|min:0',
        ]);

        $category->name            = $validated['name'];
        $category->image_size      = $validated['image_size'] ?? '2MB';
        $category->image_dimension = $validated['image_dimension'] ?? '1200 x 630 Pixels';
        $category->image_type      = $validated['image_type'] ?? '.JPG .png';
        $category->sort_order      = $validated['sort_order'];
        $category->is_active       = $request->has('is_active');
        
        // หากเปลี่ยนชื่อ หมวดหมู่จะคำนวณสลัก URL ใหม่ให้สัมพันธ์กัน
        $category->slug = null; 
        $category->save();

        return redirect()->route('admin.categories.index')->with('success', 'อัปเดตข้อมูลหมวดหมู่เรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete(); // ลบหมวดหมู่ บทความที่สังกัดอยู่จะถูกเคลียร์ตามเงื่อนไข FK Constraints

        return redirect()->route('admin.categories.index')->with('success', 'ลบหมวดหมู่เนื้อหาออกจากคลังสำเร็จ');
    }
}