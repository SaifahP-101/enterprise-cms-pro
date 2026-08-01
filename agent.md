1. System Identity & ConstraintsYAMLFramework: Laravel ^8.75
PHP_Version: ^7.3 | ^8.0 (Strict: No Enums, No Readonly Properties)
Environment: Docker Compose (Nginx 1.31.3 + PHP-FPM 8.0 + MySQL)
Style_Guide: PSR-12, RESTful Routing, Semantic UI
Theme Tokens: Core Indigo (#202040), Thai Gilded Gold (#DAA520).DOM Layout Rule: All <div class="modal"> elements must sit outside <table> and <tbody> structures to prevent DOM ejection and CSS breakage.2. Database Schema MatrixTableKey Features / Constraintsusersis_admin (boolean)menusSelf-Referencing (parent_id -> menus.id CASCADE), sort_order, is_activecategoriesname, slug (Unique)tagsname, slug (Unique)contentscategory_id, slug, type (NEWS/ACTIVITY), is_active, secure_pdf_path, view_count, SoftDeletescontent_tagPivot Table (Many-to-Many: content_id, tag_id)pagesStatic Pages, slug (Unique), secure_pdf_path, view_count, is_active, SoftDeletesslideshowsimage_path, link_url, sort_order, is_activemodal_popupsimage_path, link_url, is_active, start_date, end_date (Time-bound)audit_logsuser_id (SET NULL), action (C/U/D), old_values (JSON), new_values (JSON), Tracing IP/UA3. Implemented Core Codebase Map🌿 Module 1: Dynamic Navigation (RAM Cache Base)Observer Logic (app/Models/Menu.php): static::saved() & static::deleted() triggers Cache::forget('frontend_navigation_tree');View Injector (app/Http/View/Composers/MenuComposer.php): Caches data forever using Eager Loading with('children'). Registered in AppServiceProvider.php for layouts.frontend.🛡️ Module 4: RBAC & Audit Logs TraitSecurity Filter (app/Http/Middleware/AdminMiddleware.php): Blocks non-admin requests. Registered as admin in Kernel.php.Data Diffing engine (app/Traits/HasAuditLogs.php): Compares $model->getOriginal() against $model->getChanges(). Excludes timestamps and passwords.Query Performance (app/Http/Controllers/Admin/AccessControlController.php): Server-side DataTables JSON Gateway handling dynamic searches and paginations at the database level.🔒 Module 5 & 6: Security Guard & Performance CoreF5 Protection (app/Http/Controllers/ContentController.php): Session-based view count incrementing.SEO Gateway (app/Http/Controllers/SitemapController.php): Outputs dynamic XML to /sitemap.xml.Adaptive File Streaming (app/Http/Controllers/FileStreamController.php):PHP// Public Endpoint: /secure-stream/{filename}
$pureFilename = basename($filename);
// 1. DB Integrity Check (Content & Page is_active == true)
// 2. Multi-level path scanning (storage/app/secure_documents/ vs storage/app/)
// 3. Binary inline transmission output
JS Anti-Exploit Shield (layouts/frontend.blade.php): Disables contextmenu, intercepting Ctrl+P, Ctrl+S, Cmd+P, Cmd+S.4. Route Map Summary (routes/web.php)PHP// Public & SEO
Route::get('/sitemap.xml', [SitemapController::class, 'index']);
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/content/{slug}', [ContentController::class, 'show'])->name('contents.show');
Route::get('/page/{slug}', [PageController::class, 'show'])->name('pages.show');
Route::get('/category/{slug}', [ContentController::class, 'category'])->name('categories.view');
Route::get('/tag/{slug}', [ContentController::class, 'tag'])->name('tags.view');
Route::get('/secure-stream/{filename}', [FileStreamController::class, 'stream'])->name('secure.pdf.stream')->where('filename', '.*');

// Auth & Admin Group
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () { return view('admin.dashboard.index'); })->name('dashboard');
    Route::resource('pages', AdminPageController::class)->except(['show']);
    Route::resource('categories', AdminCategoryController::class)->except(['show']);
    Route::resource('contents', AdminContentController::class)->except(['show']);
    
    // Trash Sub-Module
    Route::get('/contents/trash', [AdminContentController::class, 'trash'])->name('contents.trash');
    Route::patch('/contents/{id}/restore', [AdminContentController::class, 'restore'])->name('contents.restore');
    Route::delete('/contents/{id}/force-delete', [AdminContentController::class, 'forceDelete'])->name('contents.force_delete');
    
    // Components & AJAX
    Route::get('/access-control/logs-data', [AccessControlController::class, 'getLogsData'])->name('access-control.logs-data');
    Route::resource('slideshows', AdminSlideshowController::class);
    Route::resource('modal-popups', AdminModalPopupController::class);
    Route::post('/media/bulk-upload', [AdminMediaController::class, 'bulkUpload'])->name('media.bulk_upload');
});
5. Next Action Items (Pending Jigsaws for AI Agent)Implement AdminContentController Methods: Complete index, create, store, edit, update utilizing HasAuditLogs and file uploads targeting the secure folder.Implement AdminCategoryController Methods: Standard Eloquent CRUD operations.Integrate Dropzone Asynchronous Core: Add bulk upload support handling the One-to-Many gallery attachment logic in AdminMediaController.Complete User CRUD Operations: Add user management capabilities within UserController.