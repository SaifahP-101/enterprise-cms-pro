<?php

namespace App\Models;

use App\Traits\HasAuditLogs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Feedback extends Model
{
    use SoftDeletes, HasAuditLogs;

    protected $table = 'feedbacks';

    protected $fillable = [
        'ticket_no',
        'type',
        'subject',
        'fullname',
        'email',
        'phone',
        'message',
        'status',
        'admin_note',
        'ip_address',
    ];

    /**
     * Boot Event: สร้าง Ticket No. อัตโนมัติก่อนบันทึกข้อมูล
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->ticket_no)) {
                $datePrefix = date('Ymd');
                $latest = static::whereDate('created_at', date('Y-m-d'))->latest('id')->first();
                $sequence = $latest ? ((int) substr($latest->ticket_no, -4)) + 1 : 1;
                $model->ticket_no = 'FB-' . $datePrefix . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}