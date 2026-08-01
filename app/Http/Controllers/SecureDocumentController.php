<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SecureDocumentController extends Controller
{
    /**
     * สตรีมมิ่งไฟล์ PDF คุ้มครองสิทธิ์ระดับองค์กร
     * 
     * @param string $filename
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\Response
     */
    public function streamPdf($filename)
    {
        // 🛡️ ด่านกักกันสิทธิ์: คนนอกที่ไม่ได้ล็อกอินจะถูกสกัดกั้นทันที
        if (!Auth::check()) {
            abort(403, 'สิทธิ์การเข้าถึงไฟล์ถูกปฏิเสธ กรุณาลงชื่อเข้าใช้งาน');
        }

        // ชี้เป้าไปที่คลังปิดพาร์ทใน (storage/app/secure_docs/)
        $filePath = 'secure_docs/' . $filename;

        if (!Storage::disk('local')->exists($filePath)) {
            abort(404, 'ไม่พบเอกสารสารสนเทศที่ระบุในคลังระบบ');
        }

        $fileRaw = Storage::disk('local')->get($filePath);
        $mimeType = Storage::disk('local')->mimeType($filePath);

        // ยิงข้อมูลออกแบบ Binary File Stream ป้องกันการแกะ URL ดาวน์โหลดตรง
        return response($fileRaw, 200)->withHeaders([
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }
}