<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class SatisfactionSummary extends Model
{
    use SoftDeletes;

    // Mass Assignment Protection
    protected $fillable = [
        'period', 
        'overall_rating', 
        'total_respondents', 
        'dimension_service', 
        'dimension_staff', 
        'dimension_facility', 
        'is_published'
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'overall_rating' => 'float',
    ];

    // Model Events สำหรับจัดการ Cache อัตโนมัติ
    protected static function boot()
    {
        parent::boot();

        // เมื่อมีการบันทึก แก้ไข หรือลบ ให้ลบ Cache หน้าบ้านทิ้ง เพื่อให้ระบบดึงข้อมูลใหม่
        $clearCache = function () {
            Cache::forget('frontend_active_satisfaction_summary');
        };

        static::saved($clearCache);
        static::deleted($clearCache);
        static::restored($clearCache);
    }
}