<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Support\Facades\Session;

class PageController extends Controller
{
    /**
     * ดึงข้อมูลหน้าเพจอิสระขึ้นแสดงผลหน้าร้านสาธารณะ
     */
    public function show($slug)
    {
        // ค้นหาเฉพาะเพจที่เปิดระบบสตรีมมิ่งออนไลน์ปกติอยู่
        $page = Page::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        // ⚡ F5 Anti-Spam View Counter Logic (Session Base Verification)
        $sessionKey = 'enterprise_cms_viewed_static_page_' . $page->id;
        if (!Session::has($sessionKey)) {
            $page->increment('view_count');
            Session::put($sessionKey, true);
        }

        return view('frontend.pages.show', compact('page'));
    }
}