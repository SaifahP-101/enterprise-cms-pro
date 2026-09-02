<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    use HasFactory;

    /**
     * ชื่อตารางในฐานข้อมูล
     *
     * @var string
     */
    protected $table = 'system_settings';

    /**
     * ฟิลด์ที่อนุญาตให้บันทึกผ่าน Mass Assignment
     *
     * @var array
     */
    protected $fillable = [
        'key',
        'value',
        'description',
    ];

    /**
     * Helper Method สำหรับดึงค่า Setting ด้วย Key อย่างรวดเร็ว
     * (ดึงผ่าน Cache เพื่อลดภาระ Database)
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getSetting($key, $default = null)
    {
        // ใช้ Laravel Cache จดจำค่าไว้ 24 ชั่วโมง เพื่อ Performance ที่ดี
        return Cache::remember('system_setting_' . $key, 86400, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Model Events
     * เคลียร์ Cache ทันทีเมื่อมีการอัปเดตหรือลบค่า Setting
     */
    protected static function booted()
    {
        static::saved(function ($setting) {
            Cache::forget('system_setting_' . $setting->key);
        });

        static::deleted(function ($setting) {
            Cache::forget('system_setting_' . $setting->key);
        });
    }
}