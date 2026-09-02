<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackupSyncedFile extends Model
{
    /**
     * 🛡️ Mass Assignment Protection
     * อนุญาตให้เพิ่มหรือแก้ไขข้อมูลเฉพาะฟิลด์ที่กำหนดเท่านั้น
     * ป้องกันผู้ไม่หวังดีส่ง Request Field อื่นๆ เข้ามาแทรกแซงฐานข้อมูล
     */
    protected $fillable = [
        'file_path', // เก็บพาธไฟล์อ้างอิงบน Local Storage
        'file_hash'  // เก็บ MD5 Hash เพื่อตรวจสอบการเปลี่ยนแปลงของไฟล์ภาพ/เอกสาร
    ];
}