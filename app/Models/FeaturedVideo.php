<?php

namespace App\Models;

use App\Traits\HasAuditLogs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeaturedVideo extends Model
{
    use SoftDeletes, HasAuditLogs;

    /**
     * Mass Assignment Protection
     */
    protected $fillable = [
        'title',
        'description',
        'youtube_url',
        'youtube_id',
        'custom_thumbnail',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Boot Event: ถอดรหัส YouTube ID อัตโนมัติก่อน Save/Update เข้าตาราง
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->isDirty('youtube_url') && !empty($model->youtube_url)) {
                $model->youtube_id = static::parseYoutubeId($model->youtube_url);
            }
        });
    }

    /**
     * Helper Function: ถอดรหัสย่อย YouTube Video ID จาก URL ทุกรูปแบบ
     * (เช่น youtube.com/watch?v=xxx, youtu.be/xxx, youtube.com/embed/xxx)
     */
    public static function parseYoutubeId($url)
    {
        $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i';
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Helper Accessor: ดึงรูป Thumbnail (ถ้าไม่มีภาพ Custom ให้ใช้ Auto Thumbnail จาก YouTube)
     */
    public function getThumbnailUrlAttribute()
    {
        if ($this->custom_thumbnail) {
            return asset('storage/' . $this->custom_thumbnail);
        }

        if ($this->youtube_id) {
            return "https://img.youtube.com/vi/{$this->youtube_id}/hqdefault.jpg";
        }

        return 'https://images.unsplash.com/photo-1541013719417-db96121c322c?auto=format&fit=crop&q=80&w=600';
    }
}