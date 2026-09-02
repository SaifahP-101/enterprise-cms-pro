<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PageController extends Controller
{
    /**
     * 📌 อนุญาตแท็ก HTML สำคัญสำหรับ CKEditor 5 (รวม h1, span, figure, table ฯลฯ)
     * เพื่อไม่ให้ strip_tags ตัดดีไซน์และขนาดฟอนต์ออก
     */
    protected $allowedHtmlTags = '<h1><h2><h3><h4><h5><h6><p><br><strong><b><i><u><s><ul><ol><li><img><table><thead><tbody><tfoot><tr><th><td><a><iframe><span><div><figure><figcaption><blockquote><hr>';

    /**
     * 📄 แสดงรายการหน้าเพจอิสระทั้งหมด พร้อมระบบค้นหาและการแบ่งหน้า (Pagination)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $pages = Page::when($request->filled('search'), function ($query) use ($request) {
                return $query->where('title', 'like', '%' . $request->search . '%')
                             ->orWhere('slug', 'like', '%' . $request->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.pages.index', compact('pages'));
    }

    /**
     * 📝 แสดงหน้าฟอร์มสร้างหน้าเพจอิสระใหม่
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.pages.create');
    }

    /**
     * 💾 บันทึกข้อมูลหน้าเพจอิสระใหม่ลงฐานข้อมูล
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'slug'             => 'nullable|string|max:255|unique:pages,slug',
            'body'             => 'required|string',
            'secure_pdf'       => 'nullable|file|mimes:pdf|max:30720', // ดักจับขนาดไม่เกิน 30MB
            'meta_title'       => 'nullable|string|max:150',
            'meta_description' => 'nullable|string|max:255',
        ], [
            'title.required'   => 'กรุณากรอกชื่อหน้าเพจอิสระ',
            'slug.unique'      => 'URL Slug นี้ถูกใช้งานแล้วในระบบ',
            'body.required'    => 'กรุณากรอกเนื้อหาประจำหน้าเพจ',
            'secure_pdf.mimes' => 'ไฟล์เอกสารแนบต้องเป็นรูปแบบ PDF เท่านั้น',
            'secure_pdf.max'   => 'ขนาดไฟล์เอกสาร PDF ต้องไม่เกิน 30MB',
        ]);

        $data = $request->except(['secure_pdf']);

        // ⚡ กรองความปลอดภัย HTML Tag โดยคงแท็กจัดแต่ง CKEditor 5 ไว้
        $data['body'] = strip_tags($data['body'], $this->allowedHtmlTags);
        $data['is_active'] = $request->has('is_active');

        // 🛡️ สร้าง Slug ภาษาไทย/อังกฤษอัตโนมัติหากไม่ได้ระบุมา
        $inputSlug = $request->filled('slug') ? $request->slug : $request->title;
        $slugBase = Str::slug($inputSlug, '-', null);
        $data['slug'] = !empty($slugBase) ? $slugBase : 'page-' . time() . '-' . rand(100, 999);

        // 🔒 Secure Storage Streaming Shield (เก็บโฟลเดอร์ปิด ป้องกันการดาวน์โหลดตรง)
        if ($request->hasFile('secure_pdf')) {
            $pdfFile = $request->file('secure_pdf');
            $customName = 'page_doc_' . time() . '_' . uniqid() . '.' . $pdfFile->getClientOriginalExtension();
            $pdfFile->storeAs('secure_docs', $customName, 'local');
            $data['secure_pdf_path'] = 'secure_docs/' . $customName;
        }

        $page = Page::create($data);

        return redirect()->route('admin.pages.edit', $page->id)
            ->with('success', 'จัดสร้างหน้าเพจอิสระสำเร็จเรียบร้อยแล้ว');
    }

    /**
     * ✏️ แสดงหน้าฟอร์มแก้ไขหน้าเพจอิสระ
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $page = Page::findOrFail($id);
        return view('admin.pages.edit', compact('page'));
    }

    /**
     * 🔄 ปรับปรุงข้อมูลหน้าเพจอิสระ
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $page = Page::findOrFail($id);

        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'slug'             => 'nullable|string|max:255|unique:pages,slug,' . $page->id,
            'body'             => 'required|string',
            'secure_pdf'       => 'nullable|file|mimes:pdf|max:30720',
            'meta_title'       => 'nullable|string|max:150',
            'meta_description' => 'nullable|string|max:255',
        ], [
            'title.required'   => 'กรุณากรอกชื่อหน้าเพจอิสระ',
            'slug.unique'      => 'URL Slug นี้ถูกใช้งานแล้วในระบบ',
            'body.required'    => 'กรุณากรอกเนื้อหาประจำหน้าเพจ',
            'secure_pdf.mimes' => 'ไฟล์เอกสารแนบต้องเป็นรูปแบบ PDF เท่านั้น',
            'secure_pdf.max'   => 'ขนาดไฟล์เอกสาร PDF ต้องไม่เกิน 30MB',
        ]);

        $data = $request->except(['secure_pdf']);

        // ⚡ คงแท็กจัดแต่ง CKEditor 5
        $data['body'] = strip_tags($data['body'], $this->allowedHtmlTags);
        $data['is_active'] = $request->has('is_active');

        // อัปเดต Slug เมื่อมีการแก้ไข
        if ($request->filled('slug') && $request->slug !== $page->slug) {
            $slugBase = Str::slug($request->slug, '-', null);
            $data['slug'] = !empty($slugBase) ? $slugBase : 'page-' . time() . '-' . rand(100, 999);
        } elseif ($page->title !== $request->title && !$request->filled('slug')) {
            $slugBase = Str::slug($request->title, '-', null);
            $data['slug'] = !empty($slugBase) ? $slugBase : 'page-' . time() . '-' . rand(100, 999);
        }

        // 🔒 จัดการไฟล์เอกสาร PDF ใหม่ พร้อมลบไฟล์เก่าออกจากดิสก์
        if ($request->hasFile('secure_pdf')) {
            if ($page->secure_pdf_path && Storage::disk('local')->exists($page->secure_pdf_path)) {
                Storage::disk('local')->delete($page->secure_pdf_path);
            }

            $pdfFile = $request->file('secure_pdf');
            $customName = 'page_doc_' . time() . '_' . uniqid() . '.' . $pdfFile->getClientOriginalExtension();
            $pdfFile->storeAs('secure_docs', $customName, 'local');
            $data['secure_pdf_path'] = 'secure_docs/' . $customName;
        }

        $page->update($data);

        return redirect()->route('admin.pages.edit', $page->id)
            ->with('success', 'ปรับปรุงข้อมูลหน้าเพจอิสระเรียบร้อยแล้ว');
    }

    /**
     * 🗑️ ลบไฟล์เอกสาร PDF ของหน้าเพจอิสระ
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removePdf($id)
    {
        $page = Page::findOrFail($id);

        if ($page->secure_pdf_path && Storage::disk('local')->exists($page->secure_pdf_path)) {
            Storage::disk('local')->delete($page->secure_pdf_path);
        }

        $page->update(['secure_pdf_path' => null]);

        return redirect()->route('admin.pages.edit', $page->id)
            ->with('success', 'ลบไฟล์เอกสาร PDF ของหน้าเพจเรียบร้อยแล้ว');
    }

    /**
     * 🗑️ ย้ายหน้าเพจลงสู่ถังขยะชั่วคราว (Soft Delete)
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $page = Page::findOrFail($id);
        $page->delete();

        return redirect()->route('admin.pages.index')
            ->with('success', 'ย้ายหน้าเพจลงสู่ถังขยะเรียบร้อยแล้ว');
    }

    /**
     * 🗄️ แสดงรายการหน้าเพจที่อยู่ในถังขยะ (Trash Vault)
     *
     * @return \Illuminate\View\View
     */
    public function trash()
    {
        $trashedPages = Page::onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->paginate(15);

        return view('admin.pages.trash', compact('trashedPages'));
    }

    /**
     * ♻️ กู้คืนหน้าเพจจากถังขยะกลับสู่ระบบปกติ
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restore($id)
    {
        $page = Page::onlyTrashed()->findOrFail($id);
        $page->restore();

        return redirect()->route('admin.pages.trash')
            ->with('success', "กู้คืนหน้าเพจ \"{$page->title}\" เรียบร้อยแล้ว ข้อมูลกลับมาแสดงผลตามปกติ");
    }

    /**
     * 💥 ทำลายหน้าเพจถาวร พร้อมลบไฟล์เอกสารแนบออกจาก Disk
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function forceDelete($id)
    {
        $page = Page::onlyTrashed()->findOrFail($id);

        if (!empty($page->secure_pdf_path) && Storage::disk('local')->exists($page->secure_pdf_path)) {
            Storage::disk('local')->delete($page->secure_pdf_path);
        }

        $page->forceDelete();

        return redirect()->route('admin.pages.trash')
            ->with('success', "ทำลายหน้าเพจแบบถาวรและลบไฟล์เอกสารแนบออกจากเซิร์ฟเวอร์เรียบร้อยแล้ว");
    }
}