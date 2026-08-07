<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentDownloadLog extends Model
{
    /**
     * ชื่อตารางในฐานข้อมูล
     * 
     * @var string
     */
    protected $table = 'content_download_logs';

    /**
     * คอลัมน์ที่อนุญาตให้ทำ Mass Assignment
     * 
     * @var array
     */
    protected $fillable = [
        'content_id',
        'fullname',
        'email',
        'phone',
        'organization',
        'purpose',
        'ip_address',
        'user_agent',
    ];

    /**
     * 🔗 Relationship: Many-to-One (ผูกกลับไปยังบทความ)
     */
    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class, 'content_id');
    }
}