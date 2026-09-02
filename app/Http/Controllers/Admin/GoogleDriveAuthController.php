<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Exception;

class GoogleDriveAuthController extends Controller
{
    private $client;

    public function __construct()
    {
        // ตั้งค่า Google Client ด้วยข้อมูลจาก .env
        $this->client = new \Google\Client();
        $this->client->setClientId(env('GOOGLE_DRIVE_CLIENT_ID'));
        $this->client->setClientSecret(env('GOOGLE_DRIVE_CLIENT_SECRET'));
        $this->client->setRedirectUri(route('admin.settings.google.callback'));
        
        // ขอสิทธิ์เฉพาะไฟล์ที่แอปพลิเคชันนี้เป็นผู้สร้าง (เพื่อความปลอดภัย ไม่ยุ่งไฟล์ส่วนตัว)
        $this->client->addScope(\Google\Service\Drive::DRIVE_FILE);
        
        // บังคับขอ Refresh Token (ต้องใช้ AccessType แบบ offline และ Prompt consent)
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
    }

    /**
     * พาผู้ใช้ไปหน้าล็อกอิน Google
     */
    public function redirectToGoogle()
    {
        $authUrl = $this->client->createAuthUrl();
        return redirect()->away($authUrl);
    }

    /**
     * รับ Callback หลังจากผู้ใช้กดยืนยันสิทธิ์จาก Google
     */
    public function handleCallback(Request $request)
    {
        if ($request->has('error')) {
            return redirect()->route('admin.settings.backup')->with('error', 'การยืนยันสิทธิ์ถูกยกเลิก: ' . $request->query('error'));
        }

        try {
            if ($request->has('code')) {
                // นำ Auth Code ไปแลกเป็น Token
                $token = $this->client->fetchAccessTokenWithAuthCode($request->query('code'));

                if (isset($token['error'])) {
                    throw new Exception($token['error_description'] ?? 'เกิดข้อผิดพลาดในการดึง Token');
                }

                // หากได้ Refresh Token มา ให้เข้ารหัสและบันทึกลงฐานข้อมูล
                if (isset($token['refresh_token'])) {
                    DB::table('system_settings')->updateOrInsert(
                        ['key' => 'google_drive_refresh_token'],
                        [
                            'value' => Crypt::encryptString($token['refresh_token']), // เข้ารหัสป้องกันข้อมูลหลุด
                            'created_at' => now(),
                            'updated_at' => now()
                        ]
                    );

                    return redirect()->route('admin.settings.backup')->with('success', 'เชื่อมต่อ Google Drive และรับ Refresh Token สำเร็จระบบพร้อมสำรองข้อมูลแล้ว');
                } else {
                    return redirect()->route('admin.settings.backup')->with('error', 'ไม่ได้รับ Refresh Token โปรดลองตัดการเชื่อมต่อบัญชีบน Google แล้วทำรายการใหม่อีกครั้ง');
                }
            }
        } catch (Exception $e) {
            Log::error('Google Drive OAuth Error: ' . $e->getMessage());
            return redirect()->route('admin.settings.backup')->with('error', 'เกิดข้อผิดพลาดในการเชื่อมต่อ: ' . $e->getMessage());
        }

        return redirect()->route('admin.settings.backup');
    }

    /**
     * ตัดการเชื่อมต่อและลบ Token
     */
    public function disconnect()
    {
        try {
            DB::table('system_settings')->where('key', 'google_drive_refresh_token')->delete();
            return redirect()->route('admin.settings.backup')->with('success', 'ตัดการเชื่อมต่อ Google Drive เรียบร้อยแล้ว');
        } catch (Exception $e) {
            return redirect()->route('admin.settings.backup')->with('error', 'เกิดข้อผิดพลาดในการยกเลิกการเชื่อมต่อ');
        }
    }
}