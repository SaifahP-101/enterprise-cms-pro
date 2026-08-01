<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentGallery;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    /**
     * 📥 Endpoint รองรับการลากวางรูปภาพชุดใหญ่ (Bulk Uploader Gateway via Dropzone.js)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkUpload(Request $request): JsonResponse
    {
        // 🛡️ ป้องกัน SQL Injection & Malicious Files ด้วยการล็อก Rules ความปลอดภัยขั้นสูง
        $request->validate([
            'content_id' => 'required|integer|exists:contents,id',
            'file'       => 'required|image|mimes:jpeg,png,jpg,webp|max:4096' // ล็อกความจุสูงสุด 4MB ต่อภาพ
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');

            // ⚡ จัดระเบียบสารสนเทศ: แยกโฟลเดอร์รูปตาม ID บทความหลัก เพื่อความคลีนในการทำระบบสำรองข้อมูล
            $destinationFolder = 'contents/galleries/' . $request->content_id;
            $savedPath = $file->store($destinationFolder, 'public');

            // บันทึกความจำลงตารางมีเดียแกลเลอรี
            $gallery = ContentGallery::create([
                'content_id' => $request->content_id,
                'file_path'  => $savedPath,
                'sort_order' => 0
            ]);

            // ส่งข้อมูลกลับเป็น JSON Response ให้ JavaScript หน้าบ้านหยิบไปเรนเดอร์ Element สดทันที
            return response()->json([
                'success' => true,
                'id'      => $gallery->id,
                'url'     => asset('storage/' . $savedPath)
            ], 200);
        }

        return response()->json(['error' => 'การจัดส่งโครงสร้างไฟล์มีเดียล้มเหลว'], 400);
    }

    /**
     * 🗑️ ระบบทำลายรูปภาพประกอบแบบไร้สาย (Asynchronous AJAX Image Destruction)
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteGalleryImage($id): JsonResponse
    {
        $galleryItem = ContentGallery::findOrFail($id);

        // 1. กวาดล้างไฟล์ภาพต้นฉบับออกจาก Local Storage ของ Docker Container เพื่อป้องกันการเกิดไฟล์ขยะ
        if ($galleryItem->file_path && Storage::disk('public')->exists($galleryItem->file_path)) {
            Storage::disk('public')->delete($galleryItem->file_path);
        }

        // 2. ลบแถวข้อมูลสถิตออกจากระบบฐานข้อมูล MySQL
        $galleryItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'ทำลายรูปภาพประกอบออกจากคลังระบบเสร็จสมบูรณ์'
        ], 200);
    }
}