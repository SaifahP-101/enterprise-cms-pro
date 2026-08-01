<?php

namespace App\Models;

use App\Traits\HasAuditLogs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasAuditLogs; // ฝังระบบประทับตราดิจิทัลจาก Phase 1

    protected $fillable = ['name', 'slug', 'image_size', 'image_dimension', 'image_type', 'sort_order', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Auto SEO Slug Generation
     */
    protected static function boot()
    {
        parent::boot();
        
        // ดักจับจังหวะเซฟข้อมูล: แปลงชื่อหมวดหมู่เป็น URL Slug ภาษาไทยอัตโนมัติ
        static::saving(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name, '-', 'th');
            }
        });
    }

    /**
     * Relation: หมวดหมู่หนึ่งชิ้น บรรจุบทความได้หลายรายการ (One-to-Many)
     */
    public function contents(): HasMany
    {
        return $this->hasMany(Content::class)->orderBy('sort_order', 'asc');
    }
}