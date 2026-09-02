<?php

namespace App\Exports;

use App\Models\EquipmentBorrow;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EquipmentBorrowExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = EquipmentBorrow::query();

        // กรองคำค้นหา
        if ($this->request->filled('search')) {
            $search = strip_tags($this->request->search);
            $query->where(function ($q) use ($search) {
                $q->where('borrower_name', 'like', "%{$search}%")
                  ->orWhere('faculty_department', 'like', "%{$search}%")
                  ->orWhere('equipment_name', 'like', "%{$search}%");
            });
        }

        // กรองช่วงวันที่ยืม
        if ($this->request->filled('start_date') && $this->request->filled('end_date')) {
            $query->borrowedBetween($this->request->start_date, $this->request->end_date);
        }

        // เรียงตามที่เลือกล่าสุด
        $sort = $this->request->get('sort', 'borrow_date');
        $direction = $this->request->get('direction', 'desc');
        
        return $query->orderBy($sort, $direction);
    }

    public function headings(): array
    {
        return [
            'ID', 'ชื่อผู้ยืม', 'สถานะผู้ยืม', 'คณะ/หน่วยงาน', 'เบอร์โทรศัพท์',
            'รายการอุปกรณ์/ครุภัณฑ์', 'จำนวน', 'วันที่ยืม', 'วันที่กำหนดคืน', 'วัตถุประสงค์', 'วันที่ลงทะเบียน'
        ];
    }

    public function map($borrow): array
    {
        return [
            $borrow->id,
            $borrow->borrower_name,
            $borrow->borrower_status,
            $borrow->faculty_department,
            $borrow->phone_number,
            $borrow->equipment_name,
            $borrow->quantity,
            $borrow->borrow_date ? $borrow->borrow_date->format('Y-m-d') : '-',
            $borrow->expected_return_date ? $borrow->expected_return_date->format('Y-m-d') : '-',
            $borrow->purpose ?? '-',
            $borrow->created_at ? $borrow->created_at->format('Y-m-d H:i:s') : '-',
        ];
    }
}