<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\Category;
use App\Models\Page;
use App\Models\EquipmentBorrow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Exports\BorrowStatsExport;
use Maatwebsite\Excel\Facades\Excel;

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

        // 2. ดึงรายการปีทั้งหมดที่มีการบันทึกจัดเก็บสารสนเทศจริงในฐานข้อมูล
        // [Enterprise Optimized] ใช้ Cache 1 ชั่วโมง + distinct() เพื่อลดภาระ RAM (OOM Prevention) ในกรณีที่ Log มีหลักล้านบรรทัด
        $availableYears = Cache::remember('dashboard_available_years', 3600, function () {
            $contentYears  = Content::selectRaw('YEAR(created_at) as year')->distinct()->pluck('year')->toArray();
            $pageYears     = Page::selectRaw('YEAR(created_at) as year')->distinct()->pluck('year')->toArray();
            
            $viewLogYears  = DB::table('content_view_logs')->selectRaw('YEAR(created_at) as year')->distinct()->pluck('year')->toArray();
            $shareLogYears = DB::table('content_share_logs')->selectRaw('YEAR(created_at) as year')->distinct()->pluck('year')->toArray();
            $downloadYears = DB::table('content_download_logs')->selectRaw('YEAR(created_at) as year')->distinct()->pluck('year')->toArray();
            
            $borrowYears   = EquipmentBorrow::selectRaw('YEAR(borrow_date) as year')->distinct()->pluck('year')->toArray();

            $years = array_unique(array_merge(
                $contentYears, $pageYears, $viewLogYears, $shareLogYears, $downloadYears, $borrowYears, [Carbon::now()->year]
            ));

            $years = array_filter($years);
            rsort($years); 
            return $years;
        });

        // 3. คำนวณยอดรวมสถิติสะสมภาพรวมหลัก (Global Core Counters)
        // [Optimized] ปรับจากเดิมที่ดึงคอลัมน์สะสม มาเป็นนับจากตาราง Log โดยตรงเพื่อความถูกต้องระดับ Event-Driven
        // ใช้ Cache 5 นาทีเพื่อป้องกันปัญหาคอขวด (Database Bottleneck) ในการนับ COUNT(*) ตารางขนาดใหญ่
        $globalStats = Cache::remember('dashboard_global_stats', 300, function () {
            return [
                'total_categories' => Category::count(),
                'total_contents'   => Content::count(), // นับจำนวนเนื้อหาที่มีอยู่ในระบบ
                'total_page_views' => (int) Page::sum('view_count'), // หน้าเพจปกติ (ถ้ายังมี)
                'total_views'      => DB::table('content_view_logs')->count(),
                'total_shares'     => DB::table('content_share_logs')->count(),
                'total_downloads'  => DB::table('content_download_logs')->count(),
            ];
        });

        $totalCategories = $globalStats['total_categories'];
        $totalContents   = $globalStats['total_contents'];
        $totalTraffic    = $globalStats['total_views'] + $globalStats['total_page_views'];
        $totalShares     = $globalStats['total_shares'];
        $totalDownloads  = $globalStats['total_downloads'];

        // 4. ดึงฟีดรายการบทความล่าสุด
        // [Anti N+1] ใช้ Eager Loading (with) เพื่อป้องกันปัญหาการยิง Query ซ้ำซ้อนตอนดึง Category ในลูป
        $recentArtifacts = Content::with('category')->orderBy('updated_at', 'desc')->take(4)->get();

        // -------------------------------------------------------------------------
        // 5. LINE TREND CHART COMPONENT QUERY (อิงจากตาราง Log ตามช่วงเวลา)
        // -------------------------------------------------------------------------
        $viewLogs = DB::table('content_view_logs')
            ->selectRaw('MONTH(created_at) as month, COUNT(id) as total')
            ->whereYear('created_at', $trendYear)
            ->groupBy('month')
            ->pluck('total', 'month');

        $shareLogs = DB::table('content_share_logs')
            ->selectRaw('MONTH(created_at) as month, COUNT(id) as total')
            ->whereYear('created_at', $trendYear)
            ->groupBy('month')
            ->pluck('total', 'month');

        $downloadLogs = DB::table('content_download_logs')
            ->selectRaw('MONTH(created_at) as month, COUNT(id) as total')
            ->whereYear('created_at', $trendYear)
            ->groupBy('month')
            ->pluck('total', 'month');

        $monthlyViews     = [];
        $monthlyShares    = [];
        $monthlyDownloads = [];

        // เติม 0 ในเดือนที่ไม่มีการทำรายการ
        for ($m = 1; $m <= 12; $m++) {
            $monthlyViews[]     = isset($viewLogs[$m]) ? (int)$viewLogs[$m] : 0;
            $monthlyShares[]    = isset($shareLogs[$m]) ? (int)$shareLogs[$m] : 0;
            $monthlyDownloads[] = isset($downloadLogs[$m]) ? (int)$downloadLogs[$m] : 0;
        }

        // -------------------------------------------------------------------------
        // 6. PER-CONTENT PERFORMANCE BAR CHART QUERY (อิงจาก Log จริง)
        // -------------------------------------------------------------------------
        // 6.1 สร้าง Base Subquery ดึงยอดวิว แชร์ ดาวน์โหลด และให้คะแนน 1 ค่าต่อ 1 แอ็กชัน
        $viewSub = DB::table('content_view_logs')
            ->select('content_id', DB::raw('1 as views'), DB::raw('0 as shares'), DB::raw('0 as downloads'))
            ->whereYear('created_at', $perfYear)
            ->when($perfMonth !== 'all', function ($q) use ($perfMonth) {
                return $q->whereMonth('created_at', $perfMonth);
            });

        $shareSub = DB::table('content_share_logs')
            ->select('content_id', DB::raw('0 as views'), DB::raw('1 as shares'), DB::raw('0 as downloads'))
            ->whereYear('created_at', $perfYear)
            ->when($perfMonth !== 'all', function ($q) use ($perfMonth) {
                return $q->whereMonth('created_at', $perfMonth);
            });

        $downloadSub = DB::table('content_download_logs')
            ->select('content_id', DB::raw('0 as views'), DB::raw('0 as shares'), DB::raw('1 as downloads'))
            ->whereYear('created_at', $perfYear)
            ->when($perfMonth !== 'all', function ($q) use ($perfMonth) {
                return $q->whereMonth('created_at', $perfMonth);
            });

        // 6.2 รวม Subquery ทั้งหมดเข้าด้วยกัน
        $unifiedPerformanceLogs = $viewSub->unionAll($shareSub)->unionAll($downloadSub);

        // 6.3 ดำเนินการ Aggregate (SUM) พร้อมผูกตัวแปรป้องกัน SQL Injection (mergeBindings)
        $topContentsData = DB::table(DB::raw("({$unifiedPerformanceLogs->toSql()}) as combined_logs"))
            ->mergeBindings($unifiedPerformanceLogs) 
            ->join('contents', 'combined_logs.content_id', '=', 'contents.id')
            ->whereNull('contents.deleted_at') // เคารพกฎ Soft Deletes
            ->select(
                'contents.id', 
                'contents.title',
                DB::raw('SUM(combined_logs.views) as view_count'),
                DB::raw('SUM(combined_logs.shares) as share_count'),
                DB::raw('SUM(combined_logs.downloads) as download_count')
            )
            ->groupBy('contents.id', 'contents.title')
            ->orderByRaw('(SUM(combined_logs.views) + SUM(combined_logs.shares) + SUM(combined_logs.downloads)) DESC')
            ->take(10)
            ->get();

        $chartContentLabels    = [];
        $chartContentViews     = [];
        $chartContentShares    = [];
        $chartContentDownloads = [];

        foreach ($topContentsData as $content) {
            // ป้องกันข้อความยาวเกินไปดันกราฟพัง
            $shortTitle = mb_strwidth($content->title, 'UTF-8') > 30 
                ? mb_strimwidth($content->title, 0, 28, '...', 'UTF-8') 
                : $content->title;

            $chartContentLabels[]    = $shortTitle;
            $chartContentViews[]     = (int)$content->view_count;
            $chartContentShares[]    = (int)$content->share_count;
            $chartContentDownloads[] = (int)$content->download_count;
        }

        // -------------------------------------------------------------------------
        // 7. EQUIPMENT BORROW STATS (รายงานระบบยืมอุปกรณ์)
        // -------------------------------------------------------------------------
        $totalBorrowTransactions = EquipmentBorrow::count();
        $totalBorrowItemsQty = (int) EquipmentBorrow::sum('quantity');

        $borrowQuery = EquipmentBorrow::whereYear('borrow_date', $perfYear)->whereNull('deleted_at');
        if ($perfMonth !== 'all') {
            $borrowQuery->whereMonth('borrow_date', $perfMonth);
        }

        // 7.3 สัดส่วนประเภทผู้ใช้ (Doughnut Chart)
        $chartBorrowUserTypes = (clone $borrowQuery)
            ->select('borrower_status', DB::raw('COUNT(*) as total'))
            ->groupBy('borrower_status')
            ->pluck('total', 'borrower_status')
            ->toArray();

        // 7.4 ท็อป 10 อุปกรณ์ที่ถูกยืมมากที่สุด ตามช่วงเวลาที่เลือก (Bar Chart)
        $chartTopEquipments = (clone $borrowQuery)
            ->select('equipment_name', DB::raw('SUM(quantity) as total_qty'))
            ->groupBy('equipment_name')
            ->orderByDesc('total_qty')
            ->take(10)
            ->pluck('total_qty', 'equipment_name')
            ->toArray();

        // รายชื่อโครงสร้างดัชนีเดือนภาษาไทยสากล
        $thaiMonths = [
            '1' => 'มกราคม', '2' => 'กุมภาพันธ์', '3' => 'มีนาคม', '4' => 'เมษายน',
            '5' => 'พฤษภาคม', '6' => 'มิถุนายน', '7' => 'กรกฎาคม', '8' => 'สิงหาคม',
            '9' => 'กันยายน', '10' => 'ตุลาคม', '11' => 'พฤศจิกายน', '12' => 'ธันวาคม'
        ];

        return view('admin.dashboard.index', compact(
            'totalCategories', 'totalContents', 'totalTraffic', 'totalShares', 'totalDownloads',
            'recentArtifacts', 'monthlyViews', 'monthlyShares', 'monthlyDownloads',
            'trendYear', 'perfYear', 'perfMonth', 'availableYears',
            'chartContentLabels', 'chartContentViews', 'chartContentShares', 'chartContentDownloads',
            'thaiMonths',
            'totalBorrowTransactions', 'totalBorrowItemsQty', 
            'chartBorrowUserTypes', 'chartTopEquipments'
        ));
    }

    /**
     * สั่งพิมพ์รายงาน Excel สถิติการยืมอุปกรณ์ ตามตัวกรอง
     * [DRY] ยุบรวมเหลือเพียงหนึ่งเมธอด จัดการการนำออกข้อมูล
     */
    public function exportStats(Request $request)
    {
        $perfYear   = $request->input('perf_year', Carbon::now()->year);
        $perfMonth  = $request->input('perf_month', 'all');

        $fileName = 'borrow_analytics_' . $perfYear . '_' . $perfMonth . '.xlsx';
        
        return Excel::download(new BorrowStatsExport($perfYear, $perfMonth), $fileName);
    }
}