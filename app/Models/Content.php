<?php

namespace App\Models;

use App\Traits\HasAuditLogs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Content extends Model
{
    use SoftDeletes, HasAuditLogs;

    /**
     * ชื่อตารางในฐานข้อมูล
     * 
     * @var string
     */
    protected $table = 'contents';

    /**
     * คอลัมน์ที่อนุญาตให้ทำ Mass Assignment
     * 
     * @var array
     */
    protected $fillable = [
        'category_id',
        'user_id',
        'title',
        'slug',
        'type',
        'body',
        'cover_image',
        'secure_pdf_path',
        'youtube_url',
        'view_count',
        'share_count',
        'download_count', // ⚡ [ADDED] เพิ่มรองรับคอลัมน์สถิติดาวน์โหลด
        'meta_title',
        'meta_description',
        'is_active',
        'published_at',
    ];

    /**
     * Casting ประเภทข้อมูลให้ถูกต้อง
     * 
     * @var array
     */
    protected $casts = [
        'is_active'      => 'boolean',
        'published_at'   => 'datetime',
        'view_count'     => 'integer',
        'share_count'    => 'integer',
        'download_count' => 'integer', // ⚡ [ADDED] กำหนด Casting
    ];

    /**
     * Auto-generate Slug หากไม่ได้มีการส่งค่ามา
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->slug)) {
                $slugBase = Str::slug($model->title, '-', null);
                $model->slug = !empty($slugBase) ? $slugBase : 'content-' . time() . '-' . rand(100, 999);
            }
        });
    }

    /**
     * 🔗 Relationship: Many-to-One (หมวดหมู่เนื้อหา)
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * 🔗 Relationship: Many-to-One (ผู้เขียน / แอดมินผู้สร้างข้อมูล)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * 🔗 Relationship: Many-to-Many (แท็กคำค้นหา)
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'content_tag');
    }

    /**
     * 🔗 Relationship: One-to-Many (คลังภาพแกลเลอรี)
     */
    public function galleries(): HasMany
    {
        return $this->hasMany(ContentGallery::class, 'content_id')->orderBy('sort_order', 'asc');
    }

    /**
     * 🔗 Relationship: One-to-Many (ประวัติผู้ขอรับเอกสารดาวน์โหลด)
     */
    public function downloadLogs(): HasMany
    {
        return $this->hasMany(ContentDownloadLog::class, 'content_id')->orderBy('created_at', 'desc');
    }

    /**
     * ความสัมพันธ์ Many-to-Many กับ User ที่มีสิทธิ์ดูแลหมวดหมู่นี้
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'category_user');
    }
}