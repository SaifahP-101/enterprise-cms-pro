<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 🏛️ 1. Import Section (นำเข้าคลาสควบคุมทั้งหมดอย่างเป็นระเบียบ)
|--------------------------------------------------------------------------
*/
// 🔐 Auth Controllers (ระบบความปลอดภัย)
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;

// 🌐 Frontend Controllers (หน้าร้านสาธารณะ)
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\FileStreamController; // 🛡️ ตัวจัดการสตรีมมิ่งไฟล์ PDF ปลอดภัย
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\Admin\FeedbackController as AdminFeedbackController;

// 🛠️ Admin Workspace Controllers (หลังบ้านแอดมิน)
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AccessControlController;
use App\Http\Controllers\Admin\MenuController as AdminMenuController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ContentController as AdminContentController;
use App\Http\Controllers\Admin\MediaController as AdminMediaController;
use App\Http\Controllers\Admin\SlideshowController as AdminSlideshowController;
use App\Http\Controllers\Admin\ModalPopupController as AdminModalPopupController;
use App\Http\Controllers\Admin\TagController as AdminTagController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FeaturedVideoController as AdminFeaturedVideoController;
use App\Http\Controllers\Admin\CalendarEventController as AdminCalendarEventController;

/*
|--------------------------------------------------------------------------
| 🌐 2. Public & Frontend Routes (หน้าร้าน แขก และ SEO)
|--------------------------------------------------------------------------
*/
// ⚡ SEO: สร้าง XML Sitemap อัตโนมัติสำหรับ Google Bot
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// 🏠 หน้าแรกหลักองค์กร
Route::get('/', [HomeController::class, 'index'])->name('home');

// 📄 หน้ารายละเอียดเนื้อหา (พ่วงระบบ Anti-F5 View Counter)
Route::get('/content/{slug}', [ContentController::class, 'show'])->name('contents.show');
Route::get('/page/{slug}', [PageController::class, 'show'])->name('pages.show');

// 🗂️ หน้าคัดกรองจัดกลุ่มสารสนเทศตามหมวดหมู่และแท็ก (แก้ไขปัญหา Route Collision เรียบร้อย)
Route::get('/category/{slug}', [ContentController::class, 'indexByCategory'])->name('contents.category');
Route::get('/tag/{slug}', [ContentController::class, 'tag'])->name('tags.view');
 
Route::post('/content/{id}/share', [ContentController::class, 'incrementShare'])
    ->name('contents.share')
    ->middleware('throttle:10,1');

// 🔒 ด่านสตรีมมิ่งเอกสารลับ PDF (เข้าถึงผ่าน Binary Response และ Database Validation)
Route::get('/secure-stream/{filename}', [FileStreamController::class, 'stream'])
    ->name('secure.pdf.stream')
    ->where('filename', '.*');

// 🌐 Public Frontend Route (ดักจับ Rate Limit ส่งได้สูงสุด 5 ครั้งต่อนาที กันสแปม)
Route::post('/feedback/store', [FeedbackController::class, 'store'])
    ->name('feedback.store')
    ->middleware('throttle:5,1');

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

// ด่านล็อกเอาท์ (ต้องล็อกอินแล้วเท่านั้น)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

/*
|--------------------------------------------------------------------------
| 🏛️ 4. Enterprise Admin Dashboard Routes (สิทธิ์แอดมินหลังบ้าน มรภ.เทพสตรี)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // 📊 4.1 หน้าแรกบอร์ดควบคุมหลัก
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // 👥 4.2 ระบบบริหารจัดการสมาชิก (User Management)
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users/store', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{id}/update', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}/destroy', [UserController::class, 'destroy'])->name('users.destroy');

    // 🛡️ 4.3 ตรวจสอบสิทธิ์และ Audit Logs (Access Control & Data Diffing)
    Route::get('/access-control', [AccessControlController::class, 'index'])->name('access-control.index');
    Route::post('/access-control/{id}/revoke', [AccessControlController::class, 'revokeAdmin'])->name('access-control.revoke');
    Route::get('/access-control/logs-data', [AccessControlController::class, 'getLogsData'])->name('access-control.logs-data'); // Server-Side DataTables

    // 🌿 4.4 ระบบจัดการเมนูควบคุมหลักไดนามิก (Menu Builder)
    Route::get('/menus', [AdminMenuController::class, 'index'])->name('menus.index');
    Route::post('/menus/store', [AdminMenuController::class, 'store'])->name('menus.store');
    Route::put('/menus/{id}/update', [AdminMenuController::class, 'update'])->name('menus.update');
    Route::delete('/menus/{id}/destroy', [AdminMenuController::class, 'destroy'])->name('menus.destroy');

    // 🗂️ 4.5 โมดูลจัดการหมวดหมู่เนื้อหา (Category CRUD + AJAX Types)
    Route::get('/categories/{category}/types', [AdminCategoryController::class, 'getTypes'])->name('categories.types');
    Route::resource('categories', AdminCategoryController::class)->except(['show']);

    // 📝 ระบบจัดการบทความและถังขยะ
    Route::get('/contents/trash', [AdminContentController::class, 'trash'])->name('contents.trash');
    Route::post('/contents/{id}/restore', [AdminContentController::class, 'restore'])->name('contents.restore');
    Route::delete('/contents/{id}/force-delete', [AdminContentController::class, 'forceDelete'])->name('contents.force-delete');
    
    // Resource Route ครอบคลุม index, create, store, edit, update, destroy
    Route::resource('contents', AdminContentController::class);

    // 📸 4.8 คลังระบบจัดส่งมีเดียแกลเลอรีกลุ่ม (Dropzone.js)
    Route::post('/media/bulk-upload', [AdminMediaController::class, 'bulkUpload'])->name('media.bulk_upload');
    Route::delete('/media/gallery/{id}/delete', [AdminMediaController::class, 'deleteGalleryImage'])->name('media.gallery.delete');

    // 🖼️ 4.9 โมดูลจัดการสไลด์โชว์หน้าแรก (Slideshow Component)
    Route::get('/slideshows', [AdminSlideshowController::class, 'index'])->name('slideshows.index');
    Route::post('/slideshows/store', [AdminSlideshowController::class, 'store'])->name('slideshows.store');
    Route::put('/slideshows/{id}/update', [AdminSlideshowController::class, 'update'])->name('slideshows.update');
    Route::delete('/slideshows/{id}/destroy', [AdminSlideshowController::class, 'destroy'])->name('slideshows.destroy');

    // 📢 4.10 โมดูลจัดการป๊อปอัปแจ้งเตือน (Modal Popup Component)
    Route::get('/modal-popups', [AdminModalPopupController::class, 'index'])->name('modal-popups.index');
    Route::post('/modal-popups/store', [AdminModalPopupController::class, 'store'])->name('modal-popups.store');
    Route::put('/modal-popups/{id}/update', [AdminModalPopupController::class, 'update'])->name('modal-popups.update');
    Route::delete('/modal-popups/{id}/destroy', [AdminModalPopupController::class, 'destroy'])->name('modal-popups.destroy');

    // 🔖 4.11 โมดูลจัดการแท็ก AJAX (Inline CRUD)
    Route::post('/tags/ajax-store', [AdminTagController::class, 'ajaxStore'])->name('tags.ajax_store');
    Route::put('/tags/ajax-update/{id}', [AdminTagController::class, 'ajaxUpdate'])->name('tags.ajax_update');
    Route::delete('/tags/ajax-destroy/{id}', [AdminTagController::class, 'ajaxDestroy'])->name('tags.ajax_destroy');

    // 💻 4.12 โมดูลหน้าเพจอิสระ (Static Pages)
    Route::resource('pages', AdminPageController::class)->except(['show']);

    // 🎥 4.13 โมดูลจัดการวิดีโอแนะนำและกิจกรรมเด่น (Featured Video Component)
    Route::get('/featured-videos', [AdminFeaturedVideoController::class, 'index'])->name('featured-videos.index');
    Route::post('/featured-videos/store', [AdminFeaturedVideoController::class, 'store'])->name('featured-videos.store');
    Route::put('/featured-videos/{id}/update', [AdminFeaturedVideoController::class, 'update'])->name('featured-videos.update');
    Route::delete('/featured-videos/{id}/destroy', [AdminFeaturedVideoController::class, 'destroy'])->name('featured-videos.destroy');

    // 📅 4.14 ระบบจัดการปฏิทินกิจกรรม (Calendar Events)
    Route::get('/calendar-events', [AdminCalendarEventController::class, 'index'])->name('calendar-events.index');
    Route::post('/calendar-events/store', [AdminCalendarEventController::class, 'store'])->name('calendar-events.store');
    Route::put('/calendar-events/{id}/update', [AdminCalendarEventController::class, 'update'])->name('calendar-events.update');
    Route::delete('/calendar-events/{id}/destroy', [AdminCalendarEventController::class, 'destroy'])->name('calendar-events.destroy');

    // 📩 4.15 ระบบบริหารจัดการข้อร้องเรียนและข้อเสนอแนะ
    Route::get('/feedbacks', [AdminFeedbackController::class, 'index'])->name('feedbacks.index');
    Route::put('/feedbacks/{id}', [AdminFeedbackController::class, 'update'])->name('feedbacks.update');
    Route::delete('/feedbacks/{id}', [AdminFeedbackController::class, 'destroy'])->name('feedbacks.destroy');
});