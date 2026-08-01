<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TagController extends Controller
{
    /**
     * 📥 บันทึกสร้างแท็กใหม่สดผ่านระบบ AJAX (Inline Create)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajaxStore(Request $request): JsonResponse
    {
        // 🛡️ ดักจับความปลอดภัยชั้นต้น ป้องกันข้อมูลขยะข้ามไซต์
        $request->validate([
            'name' => 'required|string|max:100'
        ]);

        // Smart Integrity Check: ป้องกันการบันทึกชื่อแท็กซ้ำซ้อน 
        // ถ้ามีชื่อนี้อยู่แล้วในระบบจะดึงไอดีเดิมมาส่งมอบทันที ถ้าไม่มีจะสร้างให้ใหม่สดออโต้
        $tag = Tag::firstOrCreate([
            'name' => trim($request->name)
        ]);

        return response()->json([
            'success' => true,
            'id'      => $tag->id,
            'name'    => $tag->name
        ], 200);
    }

    /**
     * 📝 ปรับปรุงแก้ไขชื่อแท็กสดๆ กลางบอร์ด Select2 (Inline Update)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajaxUpdate(Request $request, $id): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100'
        ]);

        $tag = Tag::findOrFail($id);
        $tag->name = trim($request->name);
        
        // บังคับเคลียร์ค่า Slug ภาษาไทย เพื่อให้ตัว Model Static Booting คำนวณ URL ภาษาไทยชุดใหม่ให้ถูกต้อง
        $tag->slug = null; 
        $tag->save();

        return response()->json([
            'success' => true,
            'id'      => $tag->id,
            'name'    => $tag->name
        ], 200);
    }

    /**
     * 🗑️ ถอนรากถอนโคนทำลายแท็กออกจากสารระบบคลังกลาง (Inline Destroy)
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajaxDestroy($id): JsonResponse
    {
        $tag = Tag::findOrFail($id);
        
        // 🔒 Data Integrity Lock: ปลดการเชื่อมโยงความสัมพันธ์ Many-to-Many ในตาราง Pivot ออกให้หมดก่อนลบจริง
        $tag->contents()->detach();
        $tag->delete();

        return response()->json([
            'success' => true,
            'message' => 'กวาดล้างแท็กออกจากฐานข้อมูลหลักเรียบร้อยแล้ว'
        ], 200);
    }
}