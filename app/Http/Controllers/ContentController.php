<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class ContentController extends Controller
{
    /**
     * 📄 แสดงรายละเอียดบทความเจาะลึก พร้อมกลไก F5 Anti-Spam (จากเฟสที่แล้ว)
     */
    public function show($slug)
    {
        $content = Content::with(['category', 'tags', 'galleries'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $sessionKey = 'enterprise_cms_viewed_content_' . $content->id;
        if (!Session::has($sessionKey)) {
            $content->increment('view_count');
            Session::put($sessionKey, true);
        }

        return view('frontend.contents.show', compact('content'));
    }

    /**
     * 🗂️ คัดกรองและแสดงผลบทความแยกตามหมวดหมู่หลัก (Category Filter Engine)
     */
    public function category($slug)
    {
        // ค้นหาหมวดหมู่เป้าหมายผ่าน Slug หากไม่เจอจะดีดหน้า 404 อัตโนมัติ
        $category = Category::where('slug', $slug)->firstOrFail();

        /**
         * ⚡ Anti N+1 Query Optimization:
         * บังคับใช้ Eager Loading โดนโหลดวัตถุหมวดหมู่พ่วงท้ายบทความขึ้นมาพร้อมกันในคิวรีเดียว
         * จัดทำระบบแบ่งหน้าเซิร์ฟเวอร์ละ 9 เรคคอร์ด เพื่อความเร็วในการโหลด (Performance Tuning)
         */
        $contents = Content::with('category')
            ->where('category_id', $category->id)
            ->where('is_active', true)
            ->orderBy('published_at', 'desc')
            ->paginate(9);

        return view('frontend.contents.category', compact('category', 'contents'));
    }

    /**
     * 🔖 คัดกรองและแสดงผลบทความแยกตามแท็กภูมิปัญญาท้องถิ่น (Tag Filter Engine)
     */
    public function tag($slug)
    {
        // ค้นหาคีย์เวิร์ดแท็กจากตารางหลัก
        $tag = Tag::where('slug', $slug)->firstOrFail();

        /**
         * ⚡ Many-to-Many Eager Loading Strategy:
         * ทำการเจาะคิวรีผ่านความสัมพันธ์ในตารางความสัมพันธ์เชื่อมโยง (Pivot Table) 
         * กรองเอาเฉพาะข้อมูลที่มีสถานะอนุมัติเผยแพร่เป็นหลัก
         */
        $contents = $tag->contents()
            ->with('category')
            ->where('is_active', true)
            ->orderBy('published_at', 'desc')
            ->paginate(9);

        return view('frontend.contents.tag', compact('tag', 'contents'));
    }

    public function incrementShare(Request $request, $id)
    {
        // 1. ค้นหาบทความ (ต้องเป็นบทความที่ Active เท่านั้น)
        $content = Content::where('is_active', true)->findOrFail($id);

        // 2. 🛡️ Session Anti-Spam Guard: ป้องกันผู้ใช้เดิมกดแชร์ซ้ำเพื่อปั๊มยอดในเซสชันเดียวกัน
        $sessionKey = 'shared_content_' . $content->id;

        if (!$request->session()->has($sessionKey)) {
            // หากยังไม่เคยแชร์ในเซสชันนี้ ให้เพิ่มยอด +1 ในฐานข้อมูล
            $content->increment('share_count');
            
            // ประทับตรา Session ว่าแชร์แล้ว
            $request->session()->put($sessionKey, true);

            return response()->json([
                'success' => true,
                'new_count' => $content->share_count,
                'message' => 'บันทึกยอดแชร์สำเร็จ'
            ], 200);
        }

        // หากเคยแชร์แล้ว ให้ตอบกลับว่า Success (เพื่อให้หน้าต่างแชร์เปิดปกติ) แต่ "ไม่เพิ่มยอดใน DB"
        return response()->json([
            'success' => true,
            'new_count' => $content->share_count,
            'message' => 'เคยนับสถิติการแชร์สำหรับเซสชันนี้ไปแล้ว'
        ], 200);
    }   
    
    public function indexByCategory($slug)
    {
        // ค้นหาหมวดหมู่ที่เปิดใช้งาน
        $category = Category::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        // ดึงรายการ Content ของหมวดหมู่นี้ + Eager Loading ป้องกัน N+1
        $contents = Content::with('category')
            ->where('category_id', $category->id)
            ->where('is_active', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', Carbon::now())
            ->orderBy('published_at', 'desc')
            ->paginate(12);

        return view('frontend.contents.index', compact('category', 'contents'));
    }
}