<?php

namespace App\Exports;

use App\Models\EquipmentBorrow;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class BorrowStatsExport implements FromView, ShouldAutoSize
{
    use Exportable;

    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function view(): View
    {
        $baseQuery = EquipmentBorrow::whereBetween('borrow_date', [$this->startDate, $this->endDate]);

        // ดึงสถิติพื้นฐานเพื่อส่งไปวาดใน Blade
        $stats = [
            'total_borrows' => (clone $baseQuery)->count(),
            'total_quantity' => (clone $baseQuery)->sum('quantity'),
            'user_types' => (clone $baseQuery)
                ->select('borrower_status', DB::raw('count(*) as total'))
                ->groupBy('borrower_status')
                ->get(),
            'faculties' => (clone $baseQuery)
                ->select('faculty_department', DB::raw('count(*) as total'))
                ->groupBy('faculty_department')
                ->orderByDesc('total')
                ->get(),
            'equipments' => (clone $baseQuery)
                ->select('equipment_name', DB::raw('SUM(quantity) as total_qty'), DB::raw('count(*) as total_times'))
                ->groupBy('equipment_name')
                ->orderByDesc('total_qty')
                ->get()
        ];

        // ใช้ Blade View พิเศษสำหรับจัดฟอร์แมต Excel
        return view('admin.exports.borrow_stats', [
            'stats' => $stats,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
        ]);
    }
}