<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    /**
     * แสดงรายการข้อร้องเรียนและข้อเสนอแนะหลังบ้าน
     */
    public function index(Request $request)
    {
        $query = Feedback::query();

        // กรองตามสถานะ
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        // กรองตามประเภท
        if ($request->has('type') && !empty($request->type)) {
            $query->where('type', $request->type);
        }

        // ค้นหาตาม Ticket No, Subject หรือ ชื่อผู้ส่ง
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ticket_no', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('fullname', 'like', "%{$search}%");
            });
        }

        $feedbacks = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.feedbacks.index', compact('feedbacks'));
    }

    /**
     * อัปเดตสถานะและบันทึกโน้ตของแอดมิน
     */
    public function update(Request $request, $id)
    {
        $feedback = Feedback::findOrFail($id);

        $request->validate([
            'status'     => 'required|in:PENDING,PROCESSING,RESOLVED,REJECTED',
            'admin_note' => 'nullable|string|max:1000',
        ]);

        $feedback->update([
            'status'     => $request->status,
            'admin_note' => strip_tags($request->admin_note),
        ]);

        return redirect()->back()->with('success', 'อัปเดตสถานะคำร้อง ' . $feedback->ticket_no . ' เรียบร้อยแล้ว');
    }

    /**
     * ลบรายการคำร้อง (Soft Delete)
     */
    public function destroy($id)
    {
        $feedback = Feedback::findOrFail($id);
        $feedback->delete();

        return redirect()->back()->with('success', 'ลบรายการคำร้องเรียบร้อยแล้ว');
    }
}