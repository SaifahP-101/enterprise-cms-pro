<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\Category;
use App\Models\Page;
use App\Models\ContentDownloadLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * ประมวลผลและกระจายสถิติแยกตัวกรองอิสระประจำส่วนประกอบแดชบอร์ด
     */
    public function index(Request $request)
    {
        // 1. แยกชุดคำสั่งตัวกรองแยกส่วนประกอบ (Component Isolated Parameters)
        $trendYear  = $request->input('trend_year', Carbon::now()->year);
        $perfYear   = $request->input('perf_year', Carbon::now()->year);
        $perfMonth  = $request->input('perf_month', 'all');

        // 2. ดึงรายการปีทั้งหมดที่มีการบันทึกจัดเก็บสารสนเทศจริงในฐานข้อมูล (รวมถึงประวัติการดาวน์โหลด)
        $contentYears  = Content::selectRaw('YEAR(created_at) as year')->pluck('year')->toArray();
        $pageYears     = Page::selectRaw('YEAR(created_at) as year')->pluck('year')->toArray();
        $downloadYears = DB::table('content_download_logs')->selectRaw('YEAR(created_at) as year')->pluck('year')->toArray();

        $availableYears = array_unique(array_merge($contentYears, $pageYears, $downloadYears, [Carbon::now()->year]));
        rsort($availableYears);

        // 3. คำนวณยอดรวมสถิติสะสมภาพรวมหลัก (Global Core Counters)
        $totalCategories = Category::count();
        $totalContents   = Content::count();
        $totalTraffic    = Content::sum('view_count') + Page::sum('view_count');
        $totalShares     = Content::sum('share_count');
        $totalDownloads  = Content::sum('download_count');

        // 4. ดึงฟีดรายการบทความล่าสุดผ่านสถาปัตยกรรม Eager Loading ตัดปัญหา N+1 Query
        $recentArtifacts = Content::with('category')->orderBy('updated_at', 'desc')->take(4)->get();

        /**
         * 5. LINE TREND CHART COMPONENT QUERY (กรองอิสระผ่านตัวแปร trend_year)
         * รวมศูนย์ยอดการเข้าชมระบบ ยอดแชร์ และยอดดาวน์โหลดรายเดือน
         */
        $unifiedStats = DB::table(function ($query) use ($trendYear) {
            $query->select(DB::raw('MONTH(created_at) as month'), 'view_count', 'share_count')
                ->from('contents')
                ->whereYear('created_at', $trendYear)
                ->whereNull('deleted_at')
                ->unionAll(
                    DB::table('pages')
                        ->select(DB::raw('MONTH(created_at) as month'), 'view_count', DB::raw('0 as share_count'))
                        ->whereYear('created_at', $trendYear)
                        ->whereNull('deleted_at')
                );
        }, 'unified_source')
        ->select('month', DB::raw('SUM(view_count) as total_views'), DB::raw('SUM(share_count) as total_shares'))
        ->groupBy('month')
        ->get()
        ->keyBy('month');

        // ⚡ ดึงสถิติจำนวนการดาวน์โหลดเอกสารรายเดือนจากตาราง content_download_logs ประจำปีที่เลือก
        $downloadStats = DB::table('content_download_logs')
            ->select(DB::raw('MONTH(created_at) as month'), DB::raw('COUNT(*) as total_downloads'))
            ->whereYear('created_at', $trendYear)
            ->groupBy('month')
            ->pluck('total_downloads', 'month');

        $monthlyViews     = [];
        $monthlyShares    = [];
        $monthlyDownloads = [];

        for ($m = 1; $m <= 12; $m++) {
            $monthlyViews[]     = $unifiedStats->has($m) ? (int)$unifiedStats[$m]->total_views : 0;
            $monthlyShares[]    = $unifiedStats->has($m) ? (int)$unifiedStats[$m]->total_shares : 0;
            $monthlyDownloads[] = isset($downloadStats[$m]) ? (int)$downloadStats[$m] : 0;
        }

        /**
         * 6. PER-CONTENT PERFORMANCE BAR CHART QUERY (กรองอิสระผ่าน perf_year และ perf_month)
         * คำนวณผลสัมฤทธิ์คัดเลือกสรรหาบทความยอดนิยมสูงสุด 10 อันดับแรกตามช่วงเวลาจำเพาะ (รวมยอดดาวน์โหลด)
         */
        $topContentsQuery = Content::select('id', 'title', 'view_count', 'share_count', 'download_count')
            ->whereYear('created_at', $perfYear)
            ->whereNull('deleted_at');

        if ($perfMonth !== 'all') {
            $topContentsQuery->whereMonth('created_at', $perfMonth);
        }

        $topContentsData = $topContentsQuery->orderByRaw('(view_count + share_count + download_count) DESC')
            ->take(10)
            ->get();

        $chartContentLabels    = [];
        $chartContentViews     = [];
        $chartContentShares    = [];
        $chartContentDownloads = [];

        foreach ($topContentsData as $content) {
            $shortTitle = mb_strwidth($content->title, 'UTF-8') > 30 
                ? mb_strimwidth($content->title, 0, 28, '...', 'UTF-8') 
                : $content->title;

            $chartContentLabels[]    = $shortTitle;
            $chartContentViews[]     = (int)$content->view_count;
            $chartContentShares[]    = (int)$content->share_count;
            $chartContentDownloads[] = (int)($content->download_count ?? 0);
        }

        // รายชื่อโครงสร้างดัชนีเดือนภาษาไทยสากล
        $thaiMonths = [
            '1' => 'มกราคม', '2' => 'กุมภาพันธ์', '3' => 'มีนาคม', '4' => 'เมษายน',
            '5' => 'พฤษภาคม', '6' => 'มิถุนายน', '7' => 'กรกฎาคม', '8' => 'สิงหาคม',
            '9' => 'กันยายน', '10' => 'ตุลาคม', '11' => 'พฤศจิกายน', '12' => 'ธันวาคม'
        ];

        return view('admin.dashboard.index', compact(
            'totalCategories',
            'totalContents',
            'totalTraffic',
            'totalShares',
            'totalDownloads',
            'recentArtifacts',
            'monthlyViews',
            'monthlyShares',
            'monthlyDownloads',
            'trendYear',
            'perfYear',
            'perfMonth',
            'availableYears',
            'chartContentLabels',
            'chartContentViews',
            'chartContentShares',
            'chartContentDownloads',
            'thaiMonths'
        ));
    }
}