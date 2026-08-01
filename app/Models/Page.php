<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAuditLogs;
use Illuminate\Support\Str;

class Page extends Model
{
    use SoftDeletes, HasAuditLogs; // 🛡️ ผูกระบบกู้คืนไฟล์และดักจับธุรกรรมแอดมินออโต้

    protected $fillable = [
        'title', 'slug', 'body', 'secure_pdf_path', 
        'view_count', 'is_active', 'meta_title', 'meta_description'
    ];

    protected static function boot()
    {
        parent::boot();

        // ⚡ ออโต้เจนเนอเรต Slug ภาษาไทย/อังกฤษ ก่อนที่ระบบจะทำการเซฟข้อมูลลงตาราง
        static::creating(function ($page) {
            if (empty($page->slug)) {
                // พารามิเตอร์ตัวที่สาม 'false' ของ Str::slug จะช่วยคงค่าอักขระภาษาไทยไว้ได้อย่างสมบูรณ์
                $page->slug = Str::slug($page->title, '-', false); 
            }
        });
    }
}