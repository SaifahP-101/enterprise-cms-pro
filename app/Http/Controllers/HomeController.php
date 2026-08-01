<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Slideshow;
use App\Models\ModalPopup;
use App\Models\FeaturedVideo;
use App\Models\CalendarEvent;
use Carbon\Carbon;

class HomeController extends Controller
{
    /**
     * ดึงข้อมูลแสดงผลหน้าแรก (เรียงลำดับครบ 11 Content Sections)
     */
    public function index()
    {
        $now = Carbon::now();

        // 1. Slide show
        $slideshows = Slideshow::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        // 2. แหล่งเรียนรู้ 3 บุรี (slug: three-buri-learning-resources)
        $learningResources = Content::with('category')
            ->where('category_id', 6)
            ->where('is_active', true)
            ->where('published_at', '<=', $now)
            ->latest('published_at')
            ->take(4)
            ->get();

        // 3. โครงการ/กิจกรรม (slug: projects-and-activities)
        $activities = Content::with('category')
            ->where('category_id', 2)
            ->where('is_active', true)
            ->where('published_at', '<=', $now)
            ->latest('published_at')
            ->take(3)
            ->get();

        // 4. แดชบอร์ด และงานพันธกิจสำคัญ ( Static View Component )

        // 5. หนังสือและวารสารสำนักฯ (slug: books-and-journals)
        $publications = Content::with('category')
            ->where('category_id', 5)
            ->where('is_active', true)
            ->where('published_at', '<=', $now)
            ->latest('published_at')
            ->take(4)
            ->get();

        // 6. งานวิจัยและบทความ (slug: research-and-articles)
        $researches = Content::with('category')
            ->where('category_id', 4)
            ->where('is_active', true)
            ->where('published_at', '<=', $now)
            ->latest('published_at')
            ->take(4)
            ->get();

        // 7. ข่าวประชาสัมพันธ์ (slug: news-and-announcements)
        $latestNews = Content::with('category')
            ->where('category_id', 1)
            ->where('is_active', true)
            ->where('published_at', '<=', $now)
            ->latest('published_at')
            ->take(3)
            ->get();
 
        // Popups
        $activePopup = ModalPopup::where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $now);
            })
            ->first();

        // 🎥 ดึงวิดีโอแนะนำและกิจกรรมเด่นที่เปิดใช้งาน (ตัวล่าสุด)
        $featuredVideo = FeaturedVideo::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'desc')
            ->first();

        // 📅 ตรรกะประมวลผล Calendar Grid 
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        // 1. ดึงกิจกรรมทั้งหมดในเดือนนี้
        $calendarEvents = CalendarEvent::where('is_active', true)
            ->whereBetween('event_date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
            ->get();

        // 2. ดึงกิจกรรมเด่นที่กำลังจะมาถึง (Upcoming Events)
        $upcomingEvents = CalendarEvent::where('is_active', true)
            ->whereDate('event_date', '>=', $now->format('Y-m-d'))
            ->orderBy('event_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->take(3)
            ->get();

        // 3. สร้าง Grid ปฏิทินแบบ 35 หรือ 42 ช่อง (เติมวันว่างของสัปดาห์แรกและสัปดาห์สุดท้าย)
        $calendarDays = [];
        // ย้อนกลับไปหาวันอาทิตย์แรกของสัปดาห์ (Sunday = วันเริ่มต้นสัปดาห์สากล)
        $currentGridDay = $startOfMonth->copy()->startOfWeek(Carbon::SUNDAY);
        $endGridDay = $endOfMonth->copy()->endOfWeek(Carbon::SATURDAY);

        while ($currentGridDay <= $endGridDay) {
            $dateString = $currentGridDay->format('Y-m-d');
            
            $calendarDays[] = [
                'date' => $dateString,
                'day' => $currentGridDay->day,
                'is_current_month' => $currentGridDay->month === $now->month,
                'is_today' => $currentGridDay->isToday(),
                'has_event' => $calendarEvents->contains('event_date', $currentGridDay),
            ];
            $currentGridDay->addDay();
        }

        // แปลงชื่อเดือนเป็นภาษาไทย
        $thaiMonths = ['มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];
        $currentMonthName = $thaiMonths[$now->month - 1] . ' ' . ($now->year + 543);

        return view('frontend.home', compact(
            'slideshows',
            'learningResources',
            'activities',
            'publications',
            'researches',
            'latestNews',
            'activePopup',
            'featuredVideo',
            'calendarDays',
            'currentMonthName',
            'upcomingEvents',
        ));
    }
}