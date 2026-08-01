<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Content;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ContentController extends Controller
{
    /**
     * แสดงรายการบทความทั้งหมด พร้อมระบบกรองตามหมวดหมู่
     */
    public function index(Request $request)
    {
        $categories = Category::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        $contents = Content::with('category')
            ->when($request->filled('category_id'), function ($query) use ($request) {
                return $query->where('category_id', $request->category_id);
            })
            ->latest('id')
            ->get();

        return view('admin.contents.index', compact('contents', 'categories'));
    }

    /**
     * หน้าฟอร์มสร้างบทความใหม่
     */
    public function create()
    {
        $categories = Category::where('is_active', true)->orderBy('sort_order', 'asc')->get();
        $tags = Tag::orderBy('name', 'asc')->get();

        return view('admin.contents.create', compact('categories', 'tags'));
    }

    /**
     * บันทึกข้อมูลบทความใหม่ลงฐานข้อมูล
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id'      => 'required|exists:categories,id',
            'title'            => 'required|string|max:255',
            'type'             => 'nullable|string|max:50',
            'body'             => 'required|string',
            'cover_image'      => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'secure_pdf'       => 'nullable|file|mimes:pdf|max:30720',
            'youtube_url'      => 'nullable|url|max:255',
            'meta_title'       => 'nullable|string|max:150',
            'meta_description' => 'nullable|string|max:255',
            'tags'             => 'nullable|array',
            'tags.*'           => 'integer|exists:tags,id',
        ], [
            'category_id.required' => 'กรุณาเลือกหมวดหมู่หลัก',
            'title.required'       => 'กรุณากรอกหัวข้อพาดหัวข่าว/ประกาศ',
            'body.required'        => 'กรุณากรอกเนื้อหาบทความ',
        ]);

        $data = $request->except(['cover_image', 'secure_pdf', 'tags']);
        
        // กรองความปลอดภัย HTML Tag
        $data['body'] = strip_tags($data['body'], '<p><br><strong><b><i><ul><li><ol><img><h2><h3><h4><h5><h6><table><tbody><tr><td><a><iframe>');
        $data['is_active'] = $request->has('is_active');
        $data['published_at'] = $request->published_at ?? now();
        $data['user_id'] = auth()->id();

        // 🛡️ กำหนดค่า Type อัตโนมัติ ป้องกัน MySQL Column 'type' NOT NULL Error
        if (empty($data['type'])) {
            $category = Category::find($request->category_id);
            $data['type'] = $category ? strtoupper(str_replace('-', '_', $category->slug)) : 'NEWS';
        }

        // 🛡️ สร้าง Slug ภาษาไทย/อังกฤษอัตโนมัติ
        if (empty($data['slug'])) {
            $slugBase = Str::slug($data['title'], '-', null);
            $data['slug'] = !empty($slugBase) ? $slugBase : 'content-' . time() . '-' . rand(100, 999);
        }

        // 1. จัดการรูปภาพหน้าปก (Public Storage)
        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('contents/covers', 'public');
        }

        // 2. จัดการไฟล์เอกสารสิทธิ์ PDF (Secure Storage)
        if ($request->hasFile('secure_pdf')) {
            $pdfFile = $request->file('secure_pdf');
            $customName = 'doc_' . time() . '_' . uniqid() . '.' . $pdfFile->getClientOriginalExtension();
            $pdfFile->storeAs('secure_docs', $customName, 'local');
            $data['secure_pdf_path'] = $customName;
        }

        // 3. บันทึกข้อมูลหลัก
        $content = Content::create($data);

        // 4. ซิงค์แท็ก Many-to-Many
        if (!empty($request->tags)) {
            $content->tags()->sync($request->tags);
        }

        return redirect()->route('admin.contents.index')->with('success', 'สร้างและเผยแพร่สารสนเทศเรียบร้อยแล้ว');
    }

    /**
     * หน้าฟอร์มแก้ไขบทความ
     */
    public function edit($id)
    {
        $content = Content::with(['tags', 'galleries'])->findOrFail($id);
        $categories = Category::where('is_active', true)->orderBy('sort_order', 'asc')->get();
        $tags = Tag::orderBy('name', 'asc')->get();

        return view('admin.contents.edit', compact('content', 'categories', 'tags'));
    }

    /**
     * อัปเดตข้อมูลบทความ
     */
    public function update(Request $request, $id)
    {
        $content = Content::findOrFail($id);

        $validated = $request->validate([
            'category_id'      => 'required|exists:categories,id',
            'title'            => 'required|string|max:255',
            'type'             => 'nullable|string|max:50',
            'body'             => 'required|string',
            'cover_image'      => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'secure_pdf'       => 'nullable|file|mimes:pdf|max:30720',
            'youtube_url'      => 'nullable|url|max:255',
            'meta_title'       => 'nullable|string|max:150',
            'meta_description' => 'nullable|string|max:255',
            'tags'             => 'nullable|array',
            'tags.*'           => 'integer|exists:tags,id',
        ]);

        $data = $request->except(['cover_image', 'secure_pdf', 'tags']);
        $data['body'] = strip_tags($data['body'], '<p><br><strong><b><i><ul><li><ol><img><h2><h3><h4><h5><h6><table><tbody><tr><td><a><iframe>');
        $data['is_active'] = $request->has('is_active');

        // หากมีการเปลี่ยนหัวข้อข่าว ให้อัปเดต Slug ใหม่
        if ($content->title !== $request->title) {
            $slugBase = Str::slug($request->title, '-', null);
            $data['slug'] = !empty($slugBase) ? $slugBase : 'content-' . time() . '-' . rand(100, 999);
        }

        // จัดการเปลี่ยนรูปปก
        if ($request->hasFile('cover_image')) {
            if ($content->cover_image && Storage::disk('public')->exists($content->cover_image)) {
                Storage::disk('public')->delete($content->cover_image);
            }
            $data['cover_image'] = $request->file('cover_image')->store('contents/covers', 'public');
        }

        // จัดการเปลี่ยนไฟล์ PDF
        if ($request->hasFile('secure_pdf')) {
            if ($content->secure_pdf_path && Storage::disk('local')->exists('secure_docs/' . $content->secure_pdf_path)) {
                Storage::disk('local')->delete('secure_docs/' . $content->secure_pdf_path);
            }

            $pdfFile = $request->file('secure_pdf');
            $customName = 'doc_' . time() . '_' . uniqid() . '.' . $pdfFile->getClientOriginalExtension();
            $pdfFile->storeAs('secure_docs', $customName, 'local');
            $data['secure_pdf_path'] = $customName;
        }

        $content->update($data);
        $content->tags()->sync($request->tags ?? []);

        return redirect()->route('admin.contents.index')->with('success', 'ปรับปรุงข้อมูลบทความเรียบร้อยแล้ว');
    }

    /**
     * ⚡ [FIX] ฟังก์ชันลบทิ้งชั่วคราว (Soft Delete)
     * แก้ไขปัญหาปุ่มลบในหน้า Index ไม่ทำงาน
     */
    public function destroy($id)
    {
        $content = Content::findOrFail($id);
        $content->delete(); // ทำการ Soft Delete โดยย้ายเข้าถังขยะ

        return redirect()->route('admin.contents.index')->with('success', 'ย้ายบทความลงถังขยะเรียบร้อยแล้ว');
    }

    /**
     * แสดงรายการบทความในถังขยะ
     */
    public function trash()
    {
        $trashedContents = Content::onlyTrashed()->with('category')->latest('deleted_at')->get();

        return view('admin.contents.trash', compact('trashedContents'));
    }

    /**
     * กู้คืนบทความจากถังขยะ
     */
    public function restore($id)
    {
        $content = Content::onlyTrashed()->findOrFail($id);
        $content->restore();

        return redirect()->route('admin.contents.trash')->with('success', 'กู้คืนบทความกลับสู่คลังปกติเรียบร้อยแล้ว');
    }

    /**
     * ทำลายบทความและไฟล์แนบทิ้งถาวร
     */
    public function forceDelete($id)
    {
        $content = Content::onlyTrashed()->findOrFail($id);

        // 🗑️ ลบไฟล์รูปปกจาก Public Storage
        if ($content->cover_image && Storage::disk('public')->exists($content->cover_image)) {
            Storage::disk('public')->delete($content->cover_image);
        }

        // 🔒 ลบไฟล์ PDF ปิดลับจาก Secure Local Storage
        if ($content->secure_pdf_path && Storage::disk('local')->exists('secure_docs/' . $content->secure_pdf_path)) {
            Storage::disk('local')->delete('secure_docs/' . $content->secure_pdf_path);
        }

        // ปลดความสัมพันธ์ Pivot Table
        $content->tags()->detach();

        // ลบข้อมูลถาวรออกจาก MySQL
        $content->forceDelete();

        return redirect()->route('admin.contents.trash')->with('success', 'ทำลายบทความและไฟล์แนบออกจากระบบถาวรเรียบร้อยแล้ว');
    }
}