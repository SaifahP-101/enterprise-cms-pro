<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    protected $fillable = ['name', 'slug'];

    protected static function boot()
    {
        parent::boot();
        static::saving(function ($tag) {
            if (empty($tag->slug)) {
                $slug = preg_replace('/[^a-zA-Z0-9\x{0e00}-\x{0e7f}]+/u', '-', $tag->name);
                $tag->slug = strtolower(trim($slug, '-'));
            }
        });
    }

    /**
     * Relation Many-to-Many ย้อนกลับไปหาตารางบทความหลัก
     */
    public function contents(): BelongsToMany
    {
        return $this->belongsToMany(Content::class, 'content_tag', 'tag_id', 'content_id');
    }
}