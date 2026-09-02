<?php

namespace App\Http\Controllers;

use App\Models\EquipmentBorrow;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class EquipmentBorrowController extends Controller
{
    /**
     * แสดงหน้าฟอร์มลงทะเบียนยืมอุปกรณ์
     */
    public function create()
    {
        // เรียกใช้งาน View โดยใช้ Layout หน้าบ้าน
        return view('frontend.borrow.create');
    }

    /**
     * ตรวจสอบและบันทึกข้อมูลการยืม
     */
    public function store(Request $request)
    {
        // 1. Validation ป้องกันข้อมูลขยะและ Mass Assignment
        $validated = $request->validate([
            'borrower_name' => 'required|string|max:150|regex:/^[a-zA-Zก-๙\s\.\-]+$/',
            'borrower_status' => 'required|string|in:' . EquipmentBorrow::STATUS_STUDENT . ',' . EquipmentBorrow::STATUS_STAFF . ',' . EquipmentBorrow::STATUS_EXTERNAL,
            'faculty_department' => 'required|string|max:150',
            'phone_number' => 'required|string|max:20|regex:/^([0-9\s\-\+\(\)]*)$/',
            'equipment_name' => 'required|string|max:200',
            'quantity' => 'required|integer|min:1',
            'borrow_date' => 'required|date',
            'expected_return_date' => 'required|date|after_or_equal:borrow_date',
            'purpose' => 'nullable|string|max:1000',
            // ตรวจสอบไฟล์รูปภาพ จำกัดขนาดไม่เกิน 5MB
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:5120', 
        ]);

        // 2. จัดการอัปโหลดไฟล์รูปภาพ (ถ้ามี)
        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            // เปลี่ยนชื่อไฟล์ด้วย UUID เพื่อป้องกันการโจมตีจากชื่อไฟล์แปลกปลอม (Directory Traversal)
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            // จัดเก็บไฟล์ลงใน Disk public หมวด equipment_borrows
            $imagePath = $file->storeAs('equipment_borrows', $filename, 'public');
        }

        // 3. บันทึกข้อมูล (SQL Injection ป้องกันอัตโนมัติด้วย Eloquent ORM)
        EquipmentBorrow::create([
            'borrower_name' => strip_tags($validated['borrower_name']),
            'borrower_status' => $validated['borrower_status'],
            'faculty_department' => strip_tags($validated['faculty_department']),
            'phone_number' => strip_tags($validated['phone_number']),
            'equipment_name' => strip_tags($validated['equipment_name']),
            'quantity' => $validated['quantity'],
            'borrow_date' => $validated['borrow_date'],
            'expected_return_date' => $validated['expected_return_date'],
            'purpose' => isset($validated['purpose']) ? strip_tags($validated['purpose']) : null,
            'image_path' => $imagePath,
        ]);

        // 4. เปลี่ยนเส้นทางไปยังหน้าสำเร็จ
        return redirect()->route('borrow.success')->with('success', 'บันทึกข้อมูลการยืมสำเร็จ');
    }

    /**
     * แสดงหน้าขอบคุณ / ยืนยันการทำรายการ
     */
    public function success()
    {
        return view('frontend.borrow.success');
    }
}