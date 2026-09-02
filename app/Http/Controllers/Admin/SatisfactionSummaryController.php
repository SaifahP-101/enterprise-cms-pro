<?php 

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SatisfactionSummary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SatisfactionSummaryController extends Controller
{
    public function index()
    {
        // โหลดข้อมูลล่าสุดมาแสดง พร้อมทำ Pagination
        $summaries = SatisfactionSummary::latest()->paginate(15);
        return view('admin.satisfactions.index', compact('summaries'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'period' => 'required|string|max:150',
            'overall_rating' => 'required|numeric|min:0|max:5', // คะแนนต้องอยู่ระหว่าง 0 - 5
            'total_respondents' => 'required|integer|min:0',
            'dimension_service' => 'required|integer|min:0|max:100', // เปอร์เซ็นต์ 0-100
            'dimension_staff' => 'required|integer|min:0|max:100',
            'dimension_facility' => 'required|integer|min:0|max:100',
        ]);

        // การป้องกัน XSS โดยใช้ strip_tags ก่อนบันทึก
        SatisfactionSummary::create([
            'period' => strip_tags($request->period),
            'overall_rating' => $request->overall_rating,
            'total_respondents' => $request->total_respondents,
            'dimension_service' => $request->dimension_service,
            'dimension_staff' => $request->dimension_staff,
            'dimension_facility' => $request->dimension_facility,
            'is_published' => $request->has('is_published'), // รับค่าจาก Checkbox
        ]);

        return redirect()->route('admin.satisfactions.index')
                         ->with('success', 'บันทึกข้อมูลสรุปความพึงพอใจสำเร็จ');
    }

    public function update(Request $request, SatisfactionSummary $satisfaction)
    {
        $request->validate([
            'period' => 'required|string|max:150',
            'overall_rating' => 'required|numeric|min:0|max:5',
            'total_respondents' => 'required|integer|min:0',
            'dimension_service' => 'required|integer|min:0|max:100',
            'dimension_staff' => 'required|integer|min:0|max:100',
            'dimension_facility' => 'required|integer|min:0|max:100',
        ]);

        $satisfaction->update([
            'period' => strip_tags($request->period),
            'overall_rating' => $request->overall_rating,
            'total_respondents' => $request->total_respondents,
            'dimension_service' => $request->dimension_service,
            'dimension_staff' => $request->dimension_staff,
            'dimension_facility' => $request->dimension_facility,
            'is_published' => $request->has('is_published'),
        ]);

        return redirect()->route('admin.satisfactions.index')
                         ->with('success', 'อัปเดตข้อมูลสรุปความพึงพอใจสำเร็จ');
    }

    // ฟังก์ชันสำหรับสลับสถานะ ปิด/เปิด การโชว์หน้าบ้าน (Toggle Publish)
    public function togglePublish(SatisfactionSummary $satisfaction)
    {
        // หากต้องการให้โชว์หน้าบ้านได้แค่ 1 อัน สามารถเขียน Query สั่งปิดอันอื่นได้ตรงนี้
        // SatisfactionSummary::where('id', '!=', $satisfaction->id)->update(['is_published' => false]);
        
        $satisfaction->update(['is_published' => !$satisfaction->is_published]);

        return redirect()->back()->with('success', 'อัปเดตสถานะการแสดงผลหน้าเว็บเรียบร้อยแล้ว');
    }

    public function destroy(SatisfactionSummary $satisfaction)
    {
        $satisfaction->delete(); // Soft Delete
        return redirect()->route('admin.satisfactions.index')
                         ->with('success', 'ย้ายข้อมูลลงถังขยะเรียบร้อยแล้ว');
    }
}