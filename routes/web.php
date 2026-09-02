<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan; 

/*
|--------------------------------------------------------------------------
| 🏛️ 1. Import Section (นำเข้า Controller ทั้งหมดตามมาตรฐาน PSR-12)
|--------------------------------------------------------------------------
*/
// 🔐 Auth Controllers
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;

// 🌐 Frontend Controllers
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\FileStreamController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\FeedbackController; 
use App\Http\Controllers\EquipmentBorrowController;
use App\Http\Controllers\WebCronController;

// 🛠️ Admin Workspace Controllers
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AccessControlController;
use App\Http\Controllers\Admin\RoleController as AdminRoleController;
use App\Http\Controllers\Admin\MenuController as AdminMenuController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ContentController as AdminContentController;
use App\Http\Controllers\Admin\MediaController as AdminMediaController;
use App\Http\Controllers\Admin\SlideshowController as AdminSlideshowController;
use App\Http\Controllers\Admin\ModalPopupController as AdminModalPopupController;
use App\Http\Controllers\Admin\TagController as AdminTagController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\FeaturedVideoController as AdminFeaturedVideoController;
use App\Http\Controllers\Admin\CalendarEventController as AdminCalendarEventController;
use App\Http\Controllers\Admin\FeedbackController as AdminFeedbackController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\EquipmentBorrowAdminController; 
use App\Http\Controllers\Admin\SatisfactionSummaryController;

Route::get('/system-queue-reset', function (\Illuminate\Http\Request $request) {
    // ใส่รหัสป้องกันคนนอกเข้ามายิงเล่น
    if ($request->query('token') !== 'ResetQueue2026!') {
        abort(403, 'Unauthorized Access');
    }

    try {
        // Step 1: เคลียร์ตาราง failed_jobs
        Artisan::call('queue:flush');
        $flushOutput = Artisan::output();

        // Step 3: รีสตาร์ท Queue Worker ให้โหลด Config/Memory ใหม่
        Artisan::call('queue:restart');
        $restartOutput = Artisan::output();

        return response()->json([
            'status' => 'success',
            'message' => 'ดำเนินการล้างคิวขยะและรีสตาร์ท Worker เรียบร้อยแล้ว!',
            'details' => [
                'flush' => $flushOutput,
                'restart' => $restartOutput
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
});

Route::get('/dev/composer-dump', function () {
    // 1. ระบบรักษาความปลอดภัยเบื้องต้น: ตรวจสอบ Secret Token ผ่าน URL parameter
    // ตัวอย่างการเรียกใช้งาน: https://your-domain.com/dev/composer-dump?key=MySecretKey123
    $secretKey = request('key');
    if ($secretKey !== 'MySecretKey123') {
        return response('Unauthorized: Invalid Key', 401);
    }

    // 2. ตรวจสอบว่า Server เปิดใช้งานฟังก์ชัน shell_exec หรือไม่
    if (!function_exists('shell_exec')) {
        return response('Error: shell_exec() function is disabled on this server.', 500);
    }

    try {
        // 3. รันคำสั่ง composer dump-autoload (ใช้ 2>&1 เพื่อดึง Error log ออกมาแสดงถ้าเกิดปัญหา)
        // กรณี Server หา composer ไม่เจอ อาจต้องใส่ Path เต็ม เช่น /usr/local/bin/composer
        $output = shell_exec('composer dump-autoload 2>&1');

        return response("<pre>--- Executing composer dump-autoload ---\n\n{$output}</pre>");
    } catch (\Exception $e) {
        return response('Execution Failed: ' . $e->getMessage(), 500);
    }
});

Route::get('/dev/clear-cache', function () {
    if (request('key') !== 'MySecretKey123') {
        return response('Unauthorized', 401);
    }
    // https://culture.tru.ac.th/dev/clear-cache?key=MySecretKey123
    // ล้าง Cache โครงสร้างทั้งหมดของ Laravel
    Artisan::call('optimize:clear');

    return response('<pre>Laravel Optimization Cache Cleared Successfully!</pre>');
});

// URL สำหรับให้ External Service ยิงเข้ามากระตุ้น
Route::get('/web-cron/{action}', [WebCronController::class, 'handle'])->middleware('webcron');

/*
|--------------------------------------------------------------------------
| 🌐 2. Public & Frontend Routes (หน้าร้าน แขก และ SEO)
|--------------------------------------------------------------------------
*/
// ⚡ SEO: แผนผังเว็บไซต์ XML Sitemap สากลอัตโนมัติสำหรับ Google Bot
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// 🏠 หน้าแรกหลักองค์กร
Route::get('/', [HomeController::class, 'index'])->name('home');
     
// 📚 คลังสารสนเทศหน้าบ้าน
Route::get('/contents', [ContentController::class, 'index'])->name('contents.index');
Route::get('/category/{slug}', [ContentController::class, 'indexByCategory'])->name('contents.category');
Route::get('/tag/{slug}', [ContentController::class, 'tag'])->name('tags.view');

// 📄 หน้ารายละเอียดเนื้อหาและเพจอิสระ (พ่วงระบบ Anti-F5 View Counter)
Route::get('/content/{slug}', [ContentController::class, 'show'])->name('contents.show');
Route::get('/page/{slug}', [PageController::class, 'show'])->name('pages.show');

// 📢 อัปเดตสถิติการแชร์บทความ
Route::post('/content/{id}/share', [ContentController::class, 'incrementShare'])
    ->name('contents.share')
    ->middleware('throttle:10,1');

// 🔒 สตรีมมิ่งเอกสารลับ PDF (เข้าถึงผ่าน Binary Response และ Database Validation)
Route::get('/secure-stream/{filename}', [FileStreamController::class, 'stream'])
    ->name('secure.pdf.stream')
    ->where('filename', '.*');

// 📩 ระบบรับข้อร้องเรียนและข้อเสนอแนะหน้าบ้าน
Route::post('/feedback/store', [FeedbackController::class, 'store'])
    ->name('feedback.store')
    ->middleware('throttle:5,1');

// 📥 บันทึกข้อมูลผู้ขอดาวน์โหลด + นับสถิติ + ส่ง URL ดาวน์โหลด
Route::post('/content/{id}/download', [ContentController::class, 'downloadPdf'])
    ->name('contents.download')
    ->middleware('throttle:10,1');

    // หน้าแบบฟอร์มลงทะเบียนยืม (ชี้ URL นี้ไปทำ QR Code)
Route::get('/borrow/register', [EquipmentBorrowController::class, 'create'])->name('borrow.create');

// รับค่าจากแบบฟอร์มเพื่อบันทึก
Route::post('/borrow/register', [EquipmentBorrowController::class, 'store'])->name('borrow.store');

// หน้าแสดงผลเมื่อลงทะเบียนสำเร็จ
Route::get('/borrow/success', [EquipmentBorrowController::class, 'success'])->name('borrow.success');

/*
|--------------------------------------------------------------------------
| 🔐 3. Authentication Routes (ระบบสมาชิก ล็อกอิน และกู้รหัสผ่าน)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

/*
|--------------------------------------------------------------------------
| 🏛️ 4. Enterprise Admin Workspace Routes (ระบบบริหารจัดการหลังบ้าน)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // 📊 4.1 แดชบอร์ดควบคุมหลัก
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/export', [DashboardController::class, 'exportStats'])->name('dashboard.export');

    // 👥 4.2 บริหารจัดการผู้ใช้และสิทธิ์ RBAC
    Route::middleware(['permission:manage_users'])->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users/store', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{id}/update', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}/destroy', [UserController::class, 'destroy'])->name('users.destroy');
    });

    Route::middleware(['permission:manage_roles'])->group(function () {
        Route::resource('roles', AdminRoleController::class)->except(['show']);
    });

    // 🛡️ 4.3 ตรวจสอบสิทธิ์การเข้าถึงและ Audit Logs
    Route::middleware(['permission:view_audit_logs'])->group(function () {
        Route::get('/access-control', [AccessControlController::class, 'index'])->name('access-control.index');
        Route::post('/access-control/{id}/revoke', [AccessControlController::class, 'revokeAdmin'])->name('access-control.revoke');
        Route::get('/access-control/logs-data', [AccessControlController::class, 'getLogsData'])->name('access-control.logs-data');
    });

    // 🌿 4.4 จัดการเมนูหลังบ้านและหน้าบ้านแบบไดนามิก
    Route::middleware(['permission:manage_menus'])->group(function () {
        Route::get('/menus', [AdminMenuController::class, 'index'])->name('menus.index');
        Route::post('/menus/store', [AdminMenuController::class, 'store'])->name('menus.store');
        Route::put('/menus/{id}/update', [AdminMenuController::class, 'update'])->name('menus.update');
        Route::delete('/menus/{id}/destroy', [AdminMenuController::class, 'destroy'])->name('menus.destroy');
    });

    // 🗂️ 4.5 จัดการหมวดหมู่เนื้อหา
    Route::middleware(['permission:manage_categories'])->group(function () {
        Route::get('/categories/{category}/types', [AdminCategoryController::class, 'getTypes'])->name('categories.types');
        Route::resource('categories', AdminCategoryController::class)->except(['show']);
    });

    // 📝 4.6 บริหารจัดการคลังบทความ ข่าวสาร ถังขยะ
    Route::middleware(['permission:manage_contents'])->group(function () {
        Route::get('/contents/trash', [AdminContentController::class, 'trash'])->name('contents.trash');
        Route::match(['post', 'patch'], '/contents/{id}/restore', [AdminContentController::class, 'restore'])->name('contents.restore');
        Route::delete('/contents/{id}/force-delete', [AdminContentController::class, 'forceDelete'])->name('contents.force_delete');
        
        Route::delete('/contents/{id}/remove-pdf', [AdminContentController::class, 'removePdf'])->name('contents.remove_pdf');
        Route::get('/contents/download-logs', [AdminContentController::class, 'downloadLogs'])->name('contents.download_logs');
        Route::delete('/contents/download-logs/{id}', [AdminContentController::class, 'destroyDownloadLog'])->name('contents.download_logs.destroy');
        
        Route::resource('contents', AdminContentController::class);
    });

    // 📸 4.7 คลังอัปโหลดมีเดียแกลเลอรี
    Route::middleware(['permission:manage_media'])->group(function () {
        Route::post('/media/bulk-upload', [AdminMediaController::class, 'bulkUpload'])->name('media.bulk_upload');
        Route::delete('/media/gallery/{id}/delete', [AdminMediaController::class, 'deleteGalleryImage'])->name('media.gallery.delete');
    });

    // 🖼️ 4.8 สไลด์โชว์หน้าแรกและป๊อปอัปแจ้งเตือน
    Route::middleware(['permission:manage_components'])->group(function () {
        // ... (Resource Components ของคุณ คงเดิม)
        Route::resource('slideshows', AdminSlideshowController::class)->except(['show']);
        Route::resource('modal-popups', AdminModalPopupController::class)->except(['show']);
        Route::resource('featured-videos', AdminFeaturedVideoController::class)->except(['show']);
        Route::resource('calendar-events', AdminCalendarEventController::class)->except(['show']);
    });

    // 🔖 4.9 จัดการแท็ก AJAX Inline CRUD
    Route::middleware(['permission:manage_tags'])->group(function () {
        Route::post('/tags/ajax-store', [AdminTagController::class, 'ajaxStore'])->name('tags.ajax_store');
        Route::put('/tags/ajax-update/{id}', [AdminTagController::class, 'ajaxUpdate'])->name('tags.ajax_update');
        Route::delete('/tags/ajax-destroy/{id}', [AdminTagController::class, 'ajaxDestroy'])->name('tags.ajax_destroy');
    });

    // 💻 4.10 จัดการหน้าเพจอิสระ Static Pages
    Route::middleware(['permission:manage_pages'])->group(function () {
        Route::delete('/pages/{id}/remove-pdf', [AdminPageController::class, 'removePdf'])->name('pages.remove_pdf');
        Route::resource('pages', AdminPageController::class)->except(['show']);
    });

    // 📩 4.11 ข้อร้องเรียนและข้อเสนอแนะ
    Route::middleware(['permission:view_feedbacks'])->group(function () {
        Route::resource('feedbacks', AdminFeedbackController::class)->only(['index', 'update', 'destroy']);
    });

    Route::middleware(['permission:manage_borrows'])->group(function () {
        // หน้ารายการลงทะเบียนยืม
        Route::get('/borrows', [EquipmentBorrowAdminController::class, 'index'])->name('borrows.index');
        // ลบข้อมูล (Soft Delete)
        Route::delete('/borrows/{id}', [EquipmentBorrowAdminController::class, 'destroy'])->name('borrows.destroy');
        // Export Excel
        Route::get('/borrows/export', [EquipmentBorrowAdminController::class, 'export'])->name('borrows.export'); 
    });
 
    // 🗄️ 4.13 Backup Workspace Routes
    Route::get('/settings/backup', [BackupController::class, 'index'])->name('settings.backup');
    Route::post('/settings/backup/run', [BackupController::class, 'runManualBackup'])->name('settings.backup.run');
    Route::get('/settings/backup/status', [BackupController::class, 'checkStatus'])->name('settings.backup.status');
    // docker-compose exec app php artisan cms:backup-offsite --sync

    // โมดูลสรุปความพึงพอใจ (Satisfaction Summary)
    Route::get('/satisfactions', [SatisfactionSummaryController::class, 'index'])->name('satisfactions.index');
    Route::post('/satisfactions', [SatisfactionSummaryController::class, 'store'])->name('satisfactions.store');
    Route::put('/satisfactions/{satisfaction}', [SatisfactionSummaryController::class, 'update'])->name('satisfactions.update');
    Route::delete('/satisfactions/{satisfaction}', [SatisfactionSummaryController::class, 'destroy'])->name('satisfactions.destroy');
    // เปลี่ยนสถานะการแสดงผล
    Route::patch('/satisfactions/{satisfaction}/toggle', [SatisfactionSummaryController::class, 'togglePublish'])->name('satisfactions.toggle');
    
});