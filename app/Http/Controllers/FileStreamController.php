<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\Page;

class FileStreamController extends Controller
{
    /**
     * 📥 สตรีมมิ่งไฟล์เอกสาร PDF จากโฟลเดอร์ปิดออกสู่หน้าบ้านอย่างปลอดภัย (ฉบับแก้ไขบั๊ก 404)
     *
     * @param string $filename
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function stream($filename)
    {
        // ⚡ 1. Extract Pure Filename: แยกเอาเฉพาะชื่อไฟล์บริสุทธิ์ ป้องกันพาร์ทซ้อนซ่อนเงื่อน
        // ผลลัพธ์จะได้เป็น "doc_1784542635_6a5df5abbcd6c.pdf" เสมอ
        $pureFilename = basename($filename); 
        $standardDbPath = 'secure_docs/' . $pureFilename;

        /**
         * 🔒 2. Enterprise Database Integrity Guard:
         * ค้นหาความสมมูลในฐานข้อมูลแบบยืดหยุ่น (เช็คเผื่อทั้งกรณีมีและไม่มีชื่อโฟลเดอร์นำหน้าใน DB)
         */
        $isValidContent = Content::where(function($q) use ($pureFilename, $standardDbPath) {
                                $q->where('secure_pdf_path', $pureFilename)
                                  ->orWhere('secure_pdf_path', $standardDbPath);
                            })->where('is_active', true)->exists();

        $isValidPage = Page::where(function($q) use ($pureFilename, $standardDbPath) {
                            $q->where('secure_pdf_path', $pureFilename)
                              ->orWhere('secure_pdf_path', $standardDbPath);
                        })->where('is_active', true)->exists();

        if (!$isValidContent && !$isValidPage) {
            abort(403, 'สิทธิ์การเข้าถึงไฟล์ถูกปฏิเสธ หรือไม่พบเอกสารนี้ในสารระบบสาธารณะ');
        }

        /**
         * ⚡ 3. Multi-Level Path Resolution (ค้นหาตำแหน่งไฟล์ทางกายภาพบนดิสก์)
         * ค้นหาในโฟลเดอร์ secure_docs ก่อน หากไม่เจอค่อยถอยกลับมาค้นที่ root storage
         */
        $realPath = storage_path('app/secure_docs/' . $pureFilename);
        
        if (!file_exists($realPath)) {
            $realPath = storage_path('app/' . $filename);
        }

        // 🚨 ด่านเช็คสุดท้าย: หากค้นหาทุกมิติแล้วยังไม่เจอไฟล์จริงบน Hard Drive ให้แจ้ง 404 พร้อมระบุสาเหตุชัดเจน
        if (!file_exists($realPath)) {
            abort(404, 'ไม่พบไฟล์เอกสารทางกายภาพในระบบจัดเก็บดิสก์ (Physical File Missing)');
        }

        // 4. Binary Stream Response ส่งออกแบบ Inline ปลอดภัยสูงสุด
        return response()->file($realPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $pureFilename . '"',
            'X-Content-Type-Options' => 'nosniff'
        ]);
    }
}