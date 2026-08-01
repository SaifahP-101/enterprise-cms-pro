<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * อัปเดตเพิ่ม 'is_admin' เข้าไปใน Mass Assignment Protection
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin', // เพิ่มฟิลด์สิทธิ์แอดมิน
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_admin' => 'boolean', // มั่นใจได้ว่าเป็นค่า True/False แน่นอนในชั้น Application
    ];

    /**
     * Relation: หนึ่งผู้ใช้งานสามารถมีประวัติการทำงานได้หลายรายการ (One-to-Many)
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'user_id');
    }
}