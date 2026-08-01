<?php

namespace App\Models;

use App\Traits\HasAuditLogs;
use Illuminate\Database\Eloquent\Model;

class Slideshow extends Model
{
    // 🛡️ ประทับรอยเท้าดิจิทัลเมื่อมีการเปลี่ยนแปลงแบนเนอร์
    use HasAuditLogs; 

    protected $fillable = [
        'title', 'image_path', 'link_url', 'sort_order', 'is_active'
    ];

    /**
     * ⚡ Local Scope: ดึงเฉพาะสไลด์ที่เปิดใช้งานและจัดเรียงลำดับให้พร้อม
     */
    public function scopeActiveAndSorted($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order', 'asc');
    }
}