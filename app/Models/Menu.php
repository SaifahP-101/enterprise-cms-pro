<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Menu extends Model
{
    protected $fillable = [
        'title', 
        'url', 
        'parent_id', 
        'sort_order', 
        'is_active'
    ];

    /**
     * 🔗 Relationship: Many-to-One (ดึงข้อมูลเมนูแม่)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    /**
     * 🔗 Relationship: One-to-Many (ดึงกิ่งเมนูย่อย เรียงตามลำดับความต้องการ)
     * ⚡ ป้องกัน N+1: ในการเรียกใช้ต้อง Eager Load เสมอ
     */
    public function children(): HasMany
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('sort_order', 'asc');
    }

    /**
     * ⚡ ENTERPRISE CACHE INFRASTRUCTURE CONTROL:
     * ดักจับสัญญานาฬิกาชีวิตอ็อบเจกต์ (Model Boot Events)
     * เมื่อแอดมินทำรายการ บันทึก, อัปเดต หรือสั่งทำลายเมนู 
     * ตัว Observer จะทำการทลายทิ้งแคชเก่ายกแผงทันที เพื่อให้หน้าร้านโหลดโครงสร้างใหม่ล่าสุด
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function () {
            self::clearMenuCache();
        });

        static::deleted(function () {
            self::clearMenuCache();
        });
    }

    /**
     * สั่งทลายกวาดล้างคีย์หน่วยความจำ RAM สาธารณะ
     */
    public static function clearMenuCache()
    {
        Cache::forget('frontend_navigation_tree');
    }
}