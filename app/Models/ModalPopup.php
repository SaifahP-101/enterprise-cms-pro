<?php

namespace App\Models;

use App\Traits\HasAuditLogs;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ModalPopup extends Model
{
    use HasAuditLogs;

    protected $fillable = [
        'title', 'image_path', 'link_url', 'is_active', 'start_date', 'end_date'
    ];

    // กำหนดให้ตัวแปรเหล่านี้ถูกแปลงเป็นอ็อบเจกต์ Carbon อัตโนมัติใน Laravel 8
    protected $dates = ['start_date', 'end_date'];

    /**
     * ⚡ Advanced Local Scope: กรองเฉพาะป๊อปอัปที่ตรงตามเงื่อนไขเวลาปัจจุบัน
     * ระบบนี้ช่วยให้ลดภาระ Controller หน้าแรกเขียนโค้ดสั้นลง และแม่นยำ 100%
     */
    public function scopeCurrentlyActive($query)
    {
        $now = Carbon::now();
        
        return $query->where('is_active', true)
                     ->where(function ($q) use ($now) {
                         $q->whereNull('start_date')
                           ->orWhere('start_date', '<=', $now);
                     })
                     ->where(function ($q) use ($now) {
                         $q->whereNull('end_date')
                           ->orWhere('end_date', '>=', $now);
                     });
    }
}