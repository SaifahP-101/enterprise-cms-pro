<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait HasAuditLogs
{
    /**
     * ⚡ Booting Event Hook Lifecycle:
     * ค้นหาและดักจับการเคลื่อนไหวของอ็อบเจกต์ Eloquent โดยอิงตามชื่อ Trait อัตโนมัติ
     */
    public static function bootHasAuditLogs()
    {
        // 📥 1. ดักจับจังหวะการสร้างข้อมูลใหม่ (Created Event)
        static::created(function ($model) {
            $model->logTransaction('CREATE', null, $model->getAuditAttributes());
        });

        // 📝 2. ดักจับจังหวะการแก้ไขข้อมูลสารสนเทศ (Updated Event)
        static::updated(function ($model) {
            // คำนวณหาเฉพาะส่วนต่างข้อมูลที่มีการเปลี่ยนแปลงจริง (Data Diffing Logic)
            $oldValues = array_intersect_key($model->getOriginal(), $model->getChanges());
            $newValues = $model->getChanges();

            // ล้างอัปเดตสแตมป์เวลาออกเพื่อไม่ให้ล็อกบวมขยะโดยใช่เหตุ
            unset($oldValues['updated_at'], $newValues['updated_at']);

            if (count($newValues) > 0) {
                $model->logTransaction('UPDATE', $oldValues, $newValues);
            }
        });

        // 🗑️ 3. ดักจับจังหวะการสั่งทลายลบข้อมูล (Deleted Event)
        static::deleted(function ($model) {
            $model->logTransaction('DELETE', $model->getAuditAttributes(), null);
        });
    }

    /**
     * คัดกรองข้อมูลความลับองค์กร (เช่น รหัสผ่าน) ออกจากชุดบันทึกล็อกความปลอดภัย
     */
    protected function getAuditAttributes()
    {
        $attributes = $this->toArray();
        // 🔒 Security Guard: ห้ามเก็บรหัสผ่านหรือโทเค็นลับลงระบบล็อกเด็ดขาด
        unset($attributes['password'], $attributes['remember_token'], $attributes['created_at'], $attributes['updated_at']);
        return $attributes;
    }

    /**
     * กระบวนการประกอบโครงสร้าง Payload ส่งเข้าตาราง Audit Logs
     */
    protected function logTransaction($action, $oldValues, $newValues)
    {
        // บันทึกเฉพาะเมื่อมีแอดมินล็อกอินดำเนินการทำงานอยู่จริง
        if (Auth::check()) {
            AuditLog::create([
                'user_id'        => Auth::id(),
                'action'         => $action,
                'auditable_type' => get_class($this),
                'auditable_id'   => $this->id,
                'old_values'     => $oldValues,
                'new_values'     => $newValues,
                'ip_address'     => Request::ip(),
                'user_agent'     => Request::userAgent()
            ]);
        }
    }
}