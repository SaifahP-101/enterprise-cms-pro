<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id',
        'action',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent'
    ];

    /**
     * 🛡️ มหกรรมแปลงประเภทข้อมูลอัตโนมัติ (Casting Engine)
     * บังคับให้ Laravel แปลงข้อความ Text JSON จากฐานข้อมูลให้เป็น Array ฝั่ง PHP ทันที
     */
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /**
     * 🔗 Relationship: เชื่อมกลับไปหาข้อมูลผู้ใช้งานหลังบ้าน
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withDefault([
            'name' => 'ระบบอัตโนมัติ/ผู้ใช้งานที่ถูกลบ'
        ]);
    }
}