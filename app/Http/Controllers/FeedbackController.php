<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    /**
     * บันทึกข้อมูลข้อเสนอแนะ / ร้องเรียนจากหน้าบ้าน
     */
    public function store(Request $request)
    {
        $request->validate([
            'type'     => 'required|string|in:COMPLAINT,SUGGESTION,FEEDBACK,GENERAL',
            'subject'  => 'required|string|max:255',
            'fullname' => 'required|string|max:255',
            'email'    => 'nullable|email|max:255',
            'phone'    => 'nullable|string|max:50',
            'message'  => 'required|string|max:2000',
        ], [
            'subject.required'  => 'กรุณากรอกหัวข้อเรื่อง',
            'fullname.required' => 'กรุณากรอกชื่อ-นามสกุล',
            'message.required'  => 'กรุณากรอกรายละเอียดข้อความ',
        ]);

        // ดักจับ XSS ด้วยการล้างสคริปต์ตัวหนังสือดิบ
        $feedback = Feedback::create([
            'type'       => $request->input('type'),
            'subject'    => strip_tags($request->input('subject')),
            'fullname'   => strip_tags($request->input('fullname')),
            'email'      => filter_var($request->input('email'), FILTER_SANITIZE_EMAIL),
            'phone'      => strip_tags($request->input('phone')),
            'message'    => strip_tags($request->input('message')),
            'status'     => 'PENDING',
            'ip_address' => $request->ip(),
        ]);

        return redirect()->back()->with('feedback_success', 'ส่งข้อมูลเรียบร้อยแล้ว รหัสติดตามเรื่องของคุณคือ: ' . $feedback->ticket_no);
    }
}