<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Content;
use App\Models\ContentDownloadLog;
use App\Models\ContentViewLog;
use App\Models\ContentShareLog;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ContentController extends Controller
{
    /**
     * 🌐 แสดงบทความทั้งหมด (หน้าคลังสารสนเทศรวมทุกหมวดหมู่)
     */
    public function index(Request $request)
    {
        // 1. ดึงหมวดหมู่ทั้งหมดสำหรับใส่ Dropdown Filter
        $categories = Category::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        // 2. คิวรีรายการบทความทั้งหมด + Search Filter + Eager Loading (Anti-N+1)
        $contents = Content::with(['category', 'tags'])
            ->where('is_active', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->when($request->filled('search'), function ($query) use ($request) {
                return $query->where(function ($q) use ($request) {
                    $q->where('title', 'like', '%' . $request->search . '%')
                      ->orWhere('body', 'like', '%' . $request->search . '%');
                });
            })
            ->orderBy('published_at', 'desc')
            ->paginate(12)
            ->withQueryString();

        $category = null;
        $slug = null;

        return view('frontend.contents.index', compact('categories', 'category', 'contents', 'slug'));
    }

    /**
     * 🗂️ [UPDATED] แสดงบทความแยกตามหมวดหมู่ พร้อมระบบ Search Filter
     */
    public function indexByCategory(Request $request, $slug)
    {
        // 1. ดึงหมวดหมู่ทั้งหมดสำหรับใส่ Dropdown Filter หน้าบ้าน (ป้องกัน Error $categories undefined)
        $categories = Category::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        // 2. ค้นหาหมวดหมู่ที่เปิดใช้งานตาม Slug
        $category = Category::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        // 3. ⚡ [FIXED] ดึงรายการ Content ของหมวดหมู่นี้ + ค้นหาตาม Keyword + Eager Loading
        $contents = Content::with(['category', 'tags'])
            ->where('category_id', $category->id)
            ->where('is_active', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->when($request->filled('search'), function ($query) use ($request) {
                return $query->where(function ($q) use ($request) {
                    $q->where('title', 'like', '%' . $request->search . '%')
                      ->orWhere('body', 'like', '%' . $request->search . '%');
                });
            })
            ->orderBy('published_at', 'desc')
            ->paginate(12)
            ->withQueryString();

        return view('frontend.contents.index', compact('categories', 'category', 'contents', 'slug'));
    }

    /**
     * 📄 แสดงรายละเอียดบทความเจาะลึก พร้อมกลไก F5 Anti-Spam
     */
    public function show($slug)
    {
        $content = Content::with(['category', 'tags', 'galleries'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        // 🛡️ Smart View Counter + Log Record
        $sessionKey = 'enterprise_cms_viewed_content_' . $content->id;
        if (!Session::has($sessionKey)) {
            // 1. เพิ่มยอดรวมสะสมแบบด่วน
            $content->increment('view_count');
            
            // 2. ⚡ บันทึก Log พร้อมเวลา สำหรับสถิติรายเดือน/รายปี
            ContentViewLog::create([
                'content_id' => $content->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            Session::put($sessionKey, true);
        }

        return view('frontend.contents.show', compact('content'));
    }

    /**
     * 📢 อัปเดตยอดการแชร์ผ่าน AJAX
     */
    public function incrementShare(Request $request, $id)
    {
        $content = Content::where('is_active', true)->findOrFail($id);
        $sessionKey = 'shared_content_' . $content->id;

        if (!$request->session()->has($sessionKey)) {
            // 1. เพิ่มยอดรวมสะสมแบบด่วน
            $content->increment('share_count');

            // 2. ⚡ บันทึก Log พร้อมแพลตฟอร์มและเวลา
            ContentShareLog::create([
                'content_id' => $content->id,
                'platform'   => $request->input('platform', 'facebook'),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            $request->session()->put($sessionKey, true);

            return response()->json([
                'success'   => true,
                'new_count' => $content->share_count
            ], 200);
        }

        return response()->json([
            'success'   => true,
            'new_count' => $content->share_count
        ], 200);
    }

    public function downloadPdf(Request $request, $id)
    {
        // 1. ค้นหาบทความที่มีอยู่จริง และเปิดใช้งานอยู่
        $content = Content::where('is_active', true)->findOrFail($id);

        // ตรวจสอบว่ามีไฟล์ PDF ผูกไว้หรือไม่
        if (empty($content->secure_pdf_path)) {
            return response()->json([
                'success' => false,
                'message' => 'ขออภัย ไม่พบไฟล์เอกสาร PDF สำหรับบทความนี้'
            ], 404);
        }

        // ตรวจสอบความถูกต้องของไฟล์บนเซิร์ฟเวอร์
        $safeFilename = basename($content->secure_pdf_path);
        $filePath = 'secure_docs/' . $safeFilename;

        if (!Storage::disk('local')->exists($filePath) && !Storage::disk('public')->exists($content->secure_pdf_path)) {
            return response()->json([
                'success' => false,
                'message' => 'ไฟล์เอกสารในระบบสูญหาย หรืออยู่ระหว่างการปรับปรุง'
            ], 404);
        }

        // 2. Validate ข้อมูลผู้ขอรับเอกสาร
        $validator = Validator::make($request->all(), [
            'fullname'     => 'required|string|max:255',
            'email'        => 'nullable|email|max:255',
            'phone'        => 'nullable|string|max:30',
            'organization' => 'nullable|string|max:255',
            'purpose'      => 'nullable|string|max:1000',
        ], [
            'fullname.required' => 'กรุณากรอกชื่อ-นามสกุล ผู้ขอรับเอกสาร',
            'email.email'       => 'รูปแบบอีเมลไม่ถูกต้อง',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()->all()
            ], 422);
        }

        // 3. บันทึกล็อกประวัติข้อมูลผู้ขอรับเอกสาร
        ContentDownloadLog::create([
            'content_id'   => $content->id,
            'fullname'     => $request->input('fullname'),
            'email'        => $request->input('email'),
            'phone'        => $request->input('phone'),
            'organization' => $request->input('organization'),
            'purpose'      => $request->input('purpose'),
            'ip_address'   => $request->ip(),
            'user_agent'   => $request->userAgent(),
        ]);

        // 4. เพิ่มสถิติยอดดาวน์โหลดรวม +1
        $content->increment('download_count');

        // 5. สร้าง Binary Download URL (พ่วง ?download=1 เพื่อสั่งเป็น Content-Disposition: attachment)
        $downloadUrl = route('secure.pdf.stream', ['filename' => $safeFilename]) . '?download=1';

        return response()->json([
            'success'      => true,
            'message'      => 'บันทึกข้อมูลสำเร็จ กำลังเริ่มดาวน์โหลดเอกสาร...',
            'new_count'    => $content->download_count,
            'download_url' => $downloadUrl
        ], 200);
    }
}