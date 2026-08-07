<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentViewLog extends Model
{
    protected $table = 'content_view_logs';
    protected $fillable = ['content_id', 'ip_address', 'user_agent'];

    public function content()
    {
        return $this->belongsTo(Content::class, 'content_id');
    }
}