<?php

namespace App\Models;

use App\Traits\HasAuditLogs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CalendarEvent extends Model
{
    use SoftDeletes, HasAuditLogs;

    protected $fillable = [
        'title', 'description', 'event_date', 
        'start_time', 'end_time', 'location', 'is_active'
    ];

    protected $casts = [
        'event_date' => 'date',
        'is_active'  => 'boolean',
    ];
}