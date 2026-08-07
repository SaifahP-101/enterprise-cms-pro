<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBorrowRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // อนุญาตให้บุคคลทั่วไปลงทะเบียนได้โดยไม่ต้อง Auth
    }

    public function rules(): array
    {
        return [
            'borrower_name'      => 'required|string|max:255',
            'borrower_status'    => 'required|string|in:นักศึกษา,บุคลากร,บุคคลภายนอก',
            'faculty_department' => 'required|string|max:255',
            'phone_number'       => 'required|string|max:30',
            'item_name'          => 'required|string|max:1000',
            'quantity'           => 'required|integer|min:1',
            'borrow_date'        => 'required|date',
            'due_date'           => 'required|date|after_or_equal:borrow_date',
            'purpose'            => 'nullable|string|max:1000',
            'attachment_image'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240', // ไม่เกิน 10MB
        ];
    }

    public function messages(): array
    {
        return [
            'borrower_name.required'      => 'กรุณากรอกชื่อ-นามสกุลผู้ยืม',
            'borrower_status.required'    => 'กรุณาเลือกสถานะผู้ยืม',
            'faculty_department.required' => 'กรุณากรอกคณะหรือหน่วยงาน',
            'phone_number.required'       => 'กรุณากรอกเบอร์โทรศัพท์',
            'item_name.required'          => 'กรุณาระบุรายการอุปกรณ์หรือครุภัณฑ์ที่ยืม',
            'quantity.required'           => 'กรุณากรอกจำนวนที่ยืม',
            'due_date.after_or_equal'     => 'วันที่กำหนดคืนต้องไม่น้อยกว่าวันที่ยืม',
            'attachment_image.image'      => 'ไฟล์แนบต้องเป็นรูปภาพเท่านั้น',
            'attachment_image.max'        => 'ขนาดรูปภาพต้องไม่เกิน 10MB',
        ];
    }
}