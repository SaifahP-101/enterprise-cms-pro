<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentGallery extends Model
{
    /**
     * ชื่อตารางสารสนเทศในระบบ MySQL
     *
     * @var string
     */
    protected $table = 'content_galleries';

    /**
     * 🛡️ Mass Assignment Guardrail
     * กำหนดสิทธิ์ White-listed ฟิลด์ข้อมูลที่อนุญาตให้บันทึกผ่าน Form Input ได้อย่างปลอดภัย
     *
     * @var array
     */
    protected $fillable = [
        'content_id',
        'file_path',
        'sort_order',
    ];

    /**
     * 🔗 Object-Oriented Relation: Inverse (Many-to-One)
     * เชื่อมโยงอ็อบเจกต์ภาพถ่ายเดี่ยวกลับคืนไปหาโมเดลบทความหลัก (Content Model)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class, 'content_id', 'id');
    }
}