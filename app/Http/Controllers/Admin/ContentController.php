<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Content;
use App\Models\ContentDownloadLog;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ContentController extends Controller
{
    /**
     * 📌 อนุญาตแท็ก HTML สำคัญสำหรับ CKEditor 5 (รวม h1, span, figure, table ฯลฯ)
     * เพื่อไม่ให้ strip_tags ตัดดีไซน์และขนาดฟอนต์ 18pt / 16pt / 14pt ทิ้ง
     */
    protected $allowedHtmlTags = '<h1><h2><h3><h4><h5><h6><p><br><strong><b><i><u><s><ul><ol><li><img><table><thead><tbody><tfoot><tr><th><td><a><iframe><span><div><figure><figcaption><blockquote><hr>';

    /**
     * 📄 แสดงรายการบทความทั้งหมด พร้อมระบบกรองตามหมวดหมู่ที่มีสิทธิ์เท่านั้น (Anti N+1 Query & Pagination)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $allowedCategoryIds = $user->getAccessibleCategoryIds();

        // 🛡️ ดึงเฉพาะหมวดหมู่ที่ผู้ใช้งานคนนี้มีสิทธิ์รับผิดชอบ
        $categories = Category::whereIn('id', $allowedCategoryIds)
            ->where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        // 🛡️ กรองเฉพาะบทความที่อยู่ในหมวดหมู่ซึ่งรับผิดชอบเท่านั้น
        $contents = Content::with(['category', 'user', 'tags'])
            ->whereIn('category_id', $allowedCategoryIds)
            ->when($request->filled('category_id'), function ($query) use ($request, $user) {
                if ($user->canManageCategory($request->category_id)) {
                    return $query->where('category_id', $request->category_id);
                }
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                return $query->where('title', 'like', '%' . $request->search . '%');
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.contents.index', compact('contents', 'categories'));
    }

    /**
     * 📝 หน้าฟอร์มสร้างบทความใหม่ (ส่งเฉพาะหมวดหมู่ที่มีสิทธิ์ไปแสดงใน Dropdown)
     */
    public function create()
    {
        $user = Auth::user();
        $allowedCategoryIds = $user->getAccessibleCategoryIds();

        $categories = Category::whereIn('id', $allowedCategoryIds)
            ->where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        // 🛡️ ป้องกันกรณีไม่มีหมวดหมู่ที่ได้รับอนุญาตให้ดูแล
        if ($categories->isEmpty()) {
            return redirect()->route('admin.contents.index')
                ->with('error', '🚨 คุณยังไม่ได้รับการมอบหมายสิทธิ์ให้ดูแลหมวดหมู่ใดๆ ในระบบ');
        }

        $tags = Tag::orderBy('name', 'asc')->get();

        return view('admin.contents.create', compact('categories', 'tags'));
    }

    /**
     * 💾 บันทึกข้อมูลบทความใหม่ลงฐานข้อมูล
     * ⚡ [LOGIC UPDATED] เมื่อบันทึกสำเร็จ แจ้งเตือนแล้ว Redirect ไปยังหน้า Edit ของบทความใหม่
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // 🛡️ Guard 1: ตรวจสอบสิทธิ์การสร้างบทความในหมวดหมู่ที่ส่งมา
        if (!$user->canManageCategory($request->category_id)) {
            return redirect()->back()
                ->withInput()
                ->with('error', '🚨 คุณไม่มีสิทธิ์สร้างบทความในหมวดหมู่ที่เลือก');
        }

        $validated = $request->validate([
            'category_id'      => 'required|exists:categories,id',
            'title'            => 'required|string|max:255',
            'type'             => 'nullable|string|max:50',
            'body'             => 'required|string',
            'cover_image'      => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'secure_pdf'       => 'nullable|file|mimes:pdf|max:30720', // ไม่เกิน 30MB
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
        
        // ⚡ กรองความปลอดภัย HTML Tag โดยคง h1, span, figure เพื่อให้ CKEditor 5 แสดงผลขนาดฟอนต์ 18pt/16pt/14pt ได้ตรงตามสเปก
        $data['body'] = strip_tags($data['body'], $this->allowedHtmlTags);
        $data['is_active'] = $request->has('is_active');
        $data['published_at'] = $request->published_at ?? now();
        $data['user_id'] = Auth::id();

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

        // 2. จัดการไฟล์เอกสารสิทธิ์ PDF (Secure Storage - เก็บโฟลเดอร์ปิด)
        if ($request->hasFile('secure_pdf')) {
            $pdfFile = $request->file('secure_pdf');
            $customName = 'doc_' . time() . '_' . uniqid() . '.' . $pdfFile->getClientOriginalExtension();
            $pdfFile->storeAs('secure_docs', $customName, 'local');
            $data['secure_pdf_path'] = 'secure_docs/' . $customName;
        }

        // 3. บันทึกข้อมูลหลัก
        $content = Content::create($data);

        // 4. ซิงค์แท็ก Many-to-Many
        if (!empty($request->tags)) {
            $content->tags()->sync($request->tags);
        }

        // 🎯 Redirect ไปยังหน้า Edit ของรายการที่เพิ่งสร้างขึ้น
        return redirect()->route('admin.contents.edit', $content->id)
            ->with('success', 'บันทึกสำเร็จแล้ว');
    }

    /**
     * ✏️ หน้าฟอร์มแก้ไขบทความ
     */
    public function edit($id)
    {
        $user = Auth::user();
        $content = Content::with(['tags', 'galleries'])->findOrFail($id);

        // 🛡️ Guard 2: ป้องกันการแอบเข้าถึงบทความนอกหมวดหมู่ที่ตนเองรับผิดชอบผ่านการพิมพ์ URL
        if (!$user->canManageCategory($content->category_id)) {
            abort(403, '🚨 คุณไม่มีสิทธิ์เข้าถึงหรือแก้ไขบทความในหมวดหมู่นี้');
        }

        $allowedCategoryIds = $user->getAccessibleCategoryIds();
        $categories = Category::whereIn('id', $allowedCategoryIds)
            ->where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        $tags = Tag::orderBy('name', 'asc')->get();

        return view('admin.contents.edit', compact('content', 'categories', 'tags'));
    }

    /**
     * 🔄 อัปเดตข้อมูลบทความ
     * ⚡ [LOGIC UPDATED] เมื่อแก้ไขสำเร็จ แจ้งเตือนแล้ว Redirect กลับมาหน้า Edit เดิม
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $content = Content::findOrFail($id);

        // 🛡️ Guard 3: ตรวจสอบสิทธิ์ในหมวดหมู่เดิม และหมวดหมู่ใหม่ที่ต้องการย้ายไป
        if (!$user->canManageCategory($content->category_id) || !$user->canManageCategory($request->category_id)) {
            return redirect()->back()
                ->withInput()
                ->with('error', '🚨 คุณไม่มีสิทธิ์ย้ายหรือแก้ไขบทความในหมวดหมู่นี้');
        }

        $validated = $request->validate([
            'category_id'      => 'required|exists:categories,id',
            'title'            => 'required|string|max:255',
            'type'             => 'nullable|string|max:50',
            'body'             => 'required|string',
            'cover_image'      => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'secure_pdf'       => 'nullable|file|mimes:pdf|max:30720',
            'youtube_url'      => 'nullable|url|max:255',
            'meta_title'       => 'nullable|string|max:150',
            'meta_description' => 'nullable|string|max:255',
            'tags'             => 'nullable|array',
            'tags.*'           => 'integer|exists:tags,id',
        ]);

        $data = $request->except(['cover_image', 'secure_pdf', 'tags']);
        
        // ⚡ คงแท็ก h1, span, figure ไว้เช่นกัน
        $data['body'] = strip_tags($data['body'], $this->allowedHtmlTags);
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
            if ($content->secure_pdf_path && Storage::disk('local')->exists($content->secure_pdf_path)) {
                Storage::disk('local')->delete($content->secure_pdf_path);
            }

            $pdfFile = $request->file('secure_pdf');
            $customName = 'doc_' . time() . '_' . uniqid() . '.' . $pdfFile->getClientOriginalExtension();
            $pdfFile->storeAs('secure_docs', $customName, 'local');
            $data['secure_pdf_path'] = 'secure_docs/' . $customName;
        }

        $content->update($data);
        $content->tags()->sync($request->tags ?? []);

        // 🎯 Redirect กลับมาที่หน้า Edit เดิม
        return redirect()->route('admin.contents.edit', $content->id)
            ->with('success', 'แก้ไขสำเร็จแล้ว');
    }

    /**
     * 🗑️ ฟังก์ชันลบทิ้งชั่วคราว (Soft Delete)
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $content = Content::findOrFail($id);

        // 🛡️ Guard 4: เช็กสิทธิ์ก่อนย้ายบทความลงถังขยะ
        if (!$user->canManageCategory($content->category_id)) {
            return redirect()->back()->with('error', '🚨 คุณไม่มีสิทธิ์ลบบทความในหมวดหมู่นี้');
        }

        $content->delete(); // Soft Delete ย้ายเข้าถังขยะ

        return redirect()->route('admin.contents.index')->with('success', 'ย้ายบทความลงถังขยะเรียบร้อยแล้ว');
    }

    /**
     * 🗄️ แสดงรายการบทความในถังขยะ (Trash Vault) เฉพาะหมวดหมู่ที่ตนเองรับผิดชอบ
     */
    public function trash()
    {
        $user = Auth::user();
        $allowedCategoryIds = $user->getAccessibleCategoryIds();

        $trashedContents = Content::onlyTrashed()
            ->whereIn('category_id', $allowedCategoryIds)
            ->with('category')
            ->orderBy('deleted_at', 'desc')
            ->get();

        return view('admin.contents.trash', compact('trashedContents'));
    }

    /**
     * ♻️ กู้คืนบทความจากถังขยะกลับสู่ระบบปกติ
     */
    public function restore($id)
    {
        $user = Auth::user();
        $content = Content::onlyTrashed()->findOrFail($id);

        // 🛡️ Guard 5: เช็กสิทธิ์ก่อนกู้คืนบทความ
        if (!$user->canManageCategory($content->category_id)) {
            return redirect()->back()->with('error', '🚨 คุณไม่มีสิทธิ์กู้คืนบทความในหมวดหมู่นี้');
        }

        $content->restore();

        return redirect()->route('admin.contents.trash')
            ->with('success', "กู้คืนบทความ \"{$content->title}\" เรียบร้อยแล้ว ข้อมูลกลับมาแสดงผลตามปกติ");
    }

    /**
     * 💥 ทำลายบทความถาวร พร้อมลบไฟล์รูปภาพปก/เอกสารแนบออกจาก Disk
     */
    public function forceDelete($id)
    {
        $user = Auth::user();
        $content = Content::onlyTrashed()->findOrFail($id);

        // 🛡️ Guard 6: เช็กสิทธิ์ก่อนทำลายบทความถาวร
        if (!$user->canManageCategory($content->category_id)) {
            return redirect()->back()->with('error', '🚨 คุณไม่มีสิทธิ์ทำลายบทความในหมวดหมู่นี้แบบถาวร');
        }

        if (!empty($content->cover_image) && Storage::disk('public')->exists($content->cover_image)) {
            Storage::disk('public')->delete($content->cover_image);
        }

        if (!empty($content->secure_pdf_path) && Storage::disk('local')->exists($content->secure_pdf_path)) {
            Storage::disk('local')->delete($content->secure_pdf_path);
        }

        $content->forceDelete();

        return redirect()->route('admin.contents.trash')
            ->with('success', "ทำลายบทความแบบถาวรและลบไฟล์ประกอบออกจากเซิร์ฟเวอร์เรียบร้อยแล้ว");
    }

    /**
     * 🛡️ Alias method สำหรับรองรับการเรียกแบบ snake_case force_delete
     */
    public function force_delete($id)
    {
        return $this->forceDelete($id);
    }

    /**
     * 📊 แสดงรายการบันทึกประวัติผู้ขอรับเอกสารดาวน์โหลด PDF (Download Logs Ledger)
     */
    public function downloadLogs(Request $request)
    {
        $user = Auth::user();
        $allowedCategoryIds = $user->getAccessibleCategoryIds();

        // 🛡️ ดึงเฉพาะบทความในหมวดหมู่ที่ผู้ใช้งานรับผิดชอบ
        $contentList = Content::whereIn('category_id', $allowedCategoryIds)
            ->has('downloadLogs')
            ->select('id', 'title')
            ->orderBy('title', 'asc')
            ->get();

        $logsQuery = ContentDownloadLog::whereHas('content', function ($q) use ($allowedCategoryIds) {
            $q->whereIn('category_id', $allowedCategoryIds)->withTrashed();
        })
        ->with(['content' => function ($query) {
            $query->select('id', 'title', 'slug', 'category_id', 'secure_pdf_path')->withTrashed();
        }])
        ->when($request->filled('content_id'), function ($q) use ($request, $user) {
            $content = Content::withTrashed()->find($request->content_id);
            if ($content && $user->canManageCategory($content->category_id)) {
                return $q->where('content_id', $request->content_id);
            }
        })
        ->when($request->filled('search'), function ($q) use ($request) {
            $search = $request->search;
            return $q->where(function ($sub) use ($search) {
                $sub->where('fullname', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('organization', 'like', "%{$search}%")
                    ->orWhere('purpose', 'like', "%{$search}%");
            });
        })
        ->when($request->filled('date_from'), function ($q) use ($request) {
            return $q->whereDate('created_at', '>=', $request->date_from);
        })
        ->when($request->filled('date_to'), function ($q) use ($request) {
            return $q->whereDate('created_at', '<=', $request->date_to);
        });

        $downloadLogs = $logsQuery->latest('created_at')->paginate(15)->withQueryString();

        $totalLogsCount = ContentDownloadLog::whereHas('content', function ($q) use ($allowedCategoryIds) {
            $q->whereIn('category_id', $allowedCategoryIds)->withTrashed();
        })->count();

        $filteredCount = $downloadLogs->total();

        return view('admin.contents.download_logs', compact(
            'downloadLogs',
            'contentList',
            'totalLogsCount',
            'filteredCount'
        ));
    }

    /**
     * 🗑️ ลบเรคคอร์ดประวัติผู้ขอรับเอกสารดาวน์โหลด
     */
    public function destroyDownloadLog($id)
    {
        $user = Auth::user();
        $log = ContentDownloadLog::with(['content' => function ($q) {
            $q->withTrashed();
        }])->findOrFail($id);

        // 🛡️ เช็กสิทธิ์ในหมวดหมู่ก่อนสั่งลบ Log
        if ($log->content && !$user->canManageCategory($log->content->category_id)) {
            return redirect()->back()->with('error', '🚨 คุณไม่มีสิทธิ์ลบเรคคอร์ดประวัติในหมวดหมู่นี้');
        }

        $log->delete();

        return redirect()->back()->with('success', 'ลบเรคคอร์ดประวัติการดาวน์โหลดเรียบร้อยแล้ว');
    }
}