<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\Category;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * ดึงข้อมูลโครงข่ายแผนผังสารสนเทศ ส่งออกเป็นไฟล์ text/xml สดเรียบเนียน
     */
    public function index(): Response
    {
        // ดึงเฉพาะข้อมูลบทความที่เปิดใช้งานปกติอยู่ เพื่อส่งต่อท่อข้อมูล
        $contents = Content::where('is_active', true)->orderBy('updated_at', 'desc')->get();
        $categories = Category::orderBy('created_at', 'desc')->get();

        // ⚡ ป้อนค่า Content-Type ใน Header สลักบอก Google Bot ว่านี่คือโครงสร้างไฟล์ XML สากล
        return response()
            ->view('frontend.sitemap', compact('contents', 'categories'))
            ->header('Content-Type', 'text/xml');
    }
}