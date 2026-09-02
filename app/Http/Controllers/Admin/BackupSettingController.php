<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BackupSettingController extends Controller
{
    public function index()
    {
        $isConnected = false;

        // ตรวจสอบว่ามี Token ถูกบันทึกไว้ในฐานข้อมูลหรือไม่
        if (Schema::hasTable('system_settings')) {
            $setting = DB::table('system_settings')
                ->where('key', 'google_drive_refresh_token')
                ->first();
                
            if ($setting && !empty($setting->value)) {
                $isConnected = true;
            }
        }

        return view('admin.settings.backup', compact('isConnected'));
    }
}