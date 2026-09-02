<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EquipmentBorrow;
use Illuminate\Http\Request;
use App\Exports\EquipmentBorrowExport;
use Maatwebsite\Excel\Facades\Excel;

class EquipmentBorrowAdminController extends Controller
{
    /**
     * แสดงรายการการยืมทั้งหมด พร้อมระบบ Filter
     */
    public function index(Request $request)
    {
        $query = EquipmentBorrow::query();

        // 1. ระบบค้นหา (Search) จาก ชื่อ, คณะ, อุปกรณ์
        if ($request->filled('search')) {
            $search = strip_tags($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('borrower_name', 'like', "%{$search}%")
                  ->orWhere('faculty_department', 'like', "%{$search}%")
                  ->orWhere('equipment_name', 'like', "%{$search}%");
            });
        }

        // 2. ระบบกรองช่วงวันที่ (Date Range Filter)
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->borrowedBetween($request->start_date, $request->end_date);
        }

        // 3. ระบบเรียงลำดับ (Sorting) Default เป็นวันที่ยืมล่าสุด
        $sort = $request->get('sort', 'borrow_date');
        $direction = $request->get('direction', 'desc');
        $query->orderBy($sort, $direction);

        // ทำ Pagination
        $borrows = $query->paginate(20)->withQueryString(); 
        return view('admin.borrows.index', compact('borrows'));
    }

    /**
     * ส่งออกข้อมูลเป็นไฟล์ Excel
     */
    public function export(Request $request)
    {
        return Excel::download(new EquipmentBorrowExport($request), 'equipment_borrows_'.date('YmdHis').'.xlsx');
    }

    /**
     * ลบข้อมูล (Soft Delete) เฉพาะกรณีที่จำเป็น
     */
    public function destroy($id)
    {
        $borrow = EquipmentBorrow::findOrFail($id);
        $borrow->delete(); // จะไปกระตุ้น Trait HasAuditLogs เพื่อเก็บประวัติการลบอัตโนมัติ

        return redirect()->back()->with('success', 'ลบข้อมูลสำเร็จ (ย้ายลงถังขยะเรียบร้อย)');
    }
}