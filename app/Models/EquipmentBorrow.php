<?php

namespace App\Models;

use App\Traits\HasAuditLogs;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EquipmentBorrow extends Model
{
    use HasFactory, SoftDeletes, HasAuditLogs;

    /**
     * ชื่อตารางในฐานข้อมูล
     *
     * @var string
     */
    protected $table = 'equipment_borrows';

    /**
     * Constants สำหรับสถานะผู้ยืม (แทนการใช้ PHP 8.1 Enums)
     */
    public const STATUS_STUDENT = 'นักศึกษา';
    public const STATUS_STAFF = 'บุคลากร';
    public const STATUS_EXTERNAL = 'บุคคลภายนอก';

    /**
     * ฟิลด์ที่อนุญาตให้ทำ Mass Assignment
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'borrower_name',
        'borrower_status',
        'faculty_department',
        'phone_number',
        'equipment_name',
        'quantity',
        'borrow_date',
        'expected_return_date',
        'purpose',
        'image_path',
    ];

    /**
     * การแปลงประเภทข้อมูล (Casting)
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'integer',
        'borrow_date' => 'date',
        'expected_return_date' => 'date',
    ];

    /**
     * ฟิลด์วันที่เพิ่มเติมที่ต้องจัดการเป็น Carbon instances
     * 
     * @var array<int, string>
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Scope สำหรับกรองเฉพาะช่วงวันที่ยืม (ใช้งานในส่วนระบบสรุปสถิติ Phase 4)
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $startDate (Y-m-d)
     * @param string $endDate (Y-m-d)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBorrowedBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('borrow_date', [$startDate, $endDate]);
    }
}