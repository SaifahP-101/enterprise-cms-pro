<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentShareLog extends Model
{
    protected $table = 'content_share_logs';
    protected $fillable = ['content_id', 'platform', 'ip_address', 'user_agent'];

    public function content()
    {
        return $this->belongsTo(Content::class, 'content_id');
    }
}