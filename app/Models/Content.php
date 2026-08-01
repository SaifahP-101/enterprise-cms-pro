<?php

namespace App\Models;

use App\Traits\HasAuditLogs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Content extends Model
{
    use SoftDeletes, HasAuditLogs;

    protected $table = 'contents';

    protected $fillable = [
        'category_id',
        'user_id',
        'title',
        'slug',
        'type',
        'body',
        'cover_image',
        'secure_pdf_path',
        'youtube_url',
        'view_count',
        'share_count',
        'meta_title',
        'meta_description',
        'is_active',
        'published_at',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'published_at' => 'datetime',
        'view_count'   => 'integer',
        'share_count'  => 'integer',
    ];

    /**
     * Auto-generate Slug หากไม่ได้มีการส่งค่ามา
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->slug)) {
                $slugBase = Str::slug($model->title, '-', null);
                $model->slug = !empty($slugBase) ? $slugBase : 'content-' . time() . '-' . rand(100, 999);
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'content_tag');
    }

    public function galleries()
    {
        return $this->hasMany(ContentGallery::class, 'content_id')->orderBy('sort_order', 'asc');
    }
}