<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'ระบบบริหารจัดการ') | สำนักศิลปะและวัฒนธรรม มรภ.เทพสตรี</title>
<link rel="shortcut icon" href="{{ asset('assets/images/logo-tru-culture.png') }}">

<!-- Core Typography Fonts (Local Vendor) -->
<link rel="stylesheet" href="{{ asset('vendor/fonts.css') }}">

<!-- Local Enterprise Core Stylesheets -->
<link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
<link href="{{ asset('vendor/select2/css/select2.min.css') }}" rel="stylesheet">
<link href="{{ asset('vendor/datatables/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
<link href="{{ asset('vendor/dropzone/dropzone.min.css') }}" rel="stylesheet">
<link href="{{ asset('vendor/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet">

<!-- Custom Visual Identity Theme (Thai Contemporary & Elegant Style) -->
<style>
:root {
--theme-indigo: #202040;
--theme-gold: #DAA520;
--theme-gold-light: #F4D068;
--theme-bg: #F8F9FA;
--font-header: 'Prompt', 'Noto Sans Thai', sans-serif;
--font-body: 'Noto Sans Thai', sans-serif;
}

body {
font-family: var(--font-body);
background-color: var(--theme-bg);
overflow-x: hidden;
}

.cultural-heading {
font-family: 'Sriracha', cursive;
color: var(--theme-gold);
}

/* Sidebar Architecture Styling */
#sidebar-wrapper {
min-height: 100vh;
width: 280px;
background-color: var(--theme-indigo);
transition: margin-left 0.25s ease-out;
border-right: 4px solid var(--theme-gold);
flex-shrink: 0;
}

#sidebar-wrapper .sidebar-heading {
padding: 1.5rem 1rem;
background-color: #161630;
border-bottom: 1px solid rgba(218, 165, 32, 0.2);
}

#sidebar-wrapper .list-group-item {
background-color: transparent;
color: #C2C2D6;
border: none;
padding: 0.85rem 1.5rem;
transition: all 0.2s;
font-size: 0.95rem;
}

#sidebar-wrapper .list-group-item:hover {
color: var(--theme-gold-light);
background-color: rgba(255, 255, 255, 0.05);
padding-left: 1.8rem;
}

#sidebar-wrapper .list-group-item.active {
color: #FFFFFF;
background-color: rgba(218, 165, 32, 0.15);
border-left: 4px solid var(--theme-gold);
font-weight: 500;
}

/* Responsive Toggled Layout */
#wrapper.toggled #sidebar-wrapper {
margin-left: -280px;
}

/* Top Navbar Architecture Styling */
.navbar-admin {
background-color: #FFFFFF;
border-bottom: 1px solid #E0E0E0;
padding: 0.85rem 1.5rem;
}

.btn-toggle-sidebar {
color: var(--theme-indigo);
border: 1px solid #E0E0E0;
}

.btn-toggle-sidebar:hover {
background-color: var(--theme-indigo);
color: #FFFFFF;
}

.main-content-card {
border: none;
border-radius: 8px;
box-shadow: 0 4px 12px rgba(0,0,0,0.03);
background-color: #FFFFFF;
border-top: 3px solid var(--theme-gold);
}

/* Thai Orchid Motif Border Accent at Footer */
.cultural-footer {
border-top: 2px dashed var(--theme-gold);
background-color: #FFFFFF;
font-size: 0.85rem;
color: #6C757D;
}

.style-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
.style-scrollbar::-webkit-scrollbar-thumb { background: #DAA520; border-radius: 4px; }
.style-scrollbar::-webkit-scrollbar-track { background: #F1F1F1; }
</style>
@yield('styles')
<link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
</head>
<body>

<div class="d-flex" id="wrapper">
<!-- Sidebar Layout Component -->
<div id="sidebar-wrapper" class="shadow">
<div class="sidebar-heading text-center">
<div class="mb-2">
<span class="fs-4 text-white d-block fw-bold tracking-wide">สำนักศิลปะฯ</span>
<span class="small d-block text-muted" style="color: var(--theme-gold) !important;">มรภ.เทพสตรี</span>
</div>
</div>

<div class="list-group list-group-flush mt-3 style-scrollbar" style="max-height: calc(100vh - 120px); overflow-y: auto;">

<!-- 📊 แผงควบคุมหลัก -->
<a href="{{ route('admin.dashboard') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
<i class="bi bi-speedometer2 me-1.5"></i> หน้าแรกบอร์ดควบคุม
</a>

<!-- 🌿 จัดการระบบเมนูไดนามิก -->
@if(auth()->user()->hasPermission('manage_menus'))
<a href="{{ route('admin.menus.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.menus.*') ? 'active' : '' }}">
<i class="bi bi-list-nested me-1.5"></i> จัดการเมนูอัจฉริยะ
</a>
@endif

<!-- 🗂️ จัดการหมวดหมู่เนื้อหา -->
@if(auth()->user()->hasPermission('manage_categories'))
<a href="{{ route('admin.categories.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
<i class="bi bi-folder2-open me-1.5"></i> จัดการหมวดหมู่เนื้อหา
</a>
@endif

<!-- 📝 ระบบบริหารจัดการบทความ -->
@if(auth()->user()->hasPermission('manage_contents'))
<a href="{{ route('admin.contents.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.contents.*') && !request()->routeIs('admin.contents.trash') && !request()->routeIs('admin.contents.download_logs') ? 'active' : '' }}">
<i class="bi bi-pencil-square me-1.5"></i> ระบบเขียนข่าวและกิจกรรม
</a>
@endif

<!-- 💻 จัดการหน้าเพจอิสระ -->
@if(auth()->user()->hasPermission('manage_pages'))
<a href="{{ route('admin.pages.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.pages.*') ? 'active' : '' }}">
<i class="bi bi-file-earmark-richtext me-1.5"></i> จัดการหน้าเพจอิสระ
</a>
@endif

<!-- 🗑️ ถังขยะกู้คืนบทความ -->
@if(auth()->user()->hasPermission('manage_contents'))
<a href="{{ route('admin.contents.trash') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.contents.trash') ? 'active' : '' }} text-info">
<i class="bi bi-trash3 me-1.5"></i> ถังขยะกู้คืนบทความ
</a>
@endif

<!-- ========================================== -->
<!-- 📌 SECTION: ระบบบริการยืม-คืนอุปกรณ์ (Borrow System) -->
<!-- ========================================== -->
@if(auth()->user()->hasPermission('manage_borrows') || auth()->user()->is_admin)
<hr class="text-secondary mx-3 my-2 opacity-25">
<div class="px-3 py-1 small text-uppercase text-muted fw-bold" style="font-size: 0.75rem; color: var(--theme-gold) !important;">
ระบบยืม-คืนอุปกรณ์
</div>

<a href="{{ route('admin.borrows.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.borrows.*') ? 'active' : '' }}">
<i class="bi bi-box-seam me-1.5"></i> ข้อมูลการลงทะเบียนยืม
</a>
@endif
<!-- ========================================== -->

<!-- 📌 SECTION: Component หน้าแรก -->
@if(auth()->user()->hasPermission('manage_components') || auth()->user()->hasPermission('view_feedbacks') || auth()->user()->hasPermission('manage_contents'))
<hr class="text-secondary mx-3 my-2 opacity-25">
<div class="px-3 py-1 small text-uppercase text-muted fw-bold" style="font-size: 0.75rem; color: var(--theme-gold) !important;">
Component หน้าแรก
</div>

<!-- สไลด์แบนเนอร์หน้าแรก -->
@if(auth()->user()->hasPermission('manage_components'))
<a href="{{ route('admin.slideshows.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.slideshows.*') ? 'active' : '' }}">
<i class="bi bi-images me-1.5"></i> จัดการแบนเนอร์หน้าแรก
</a>

<a href="{{ route('admin.featured-videos.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.featured-videos.*') ? 'active' : '' }}">
<i class="bi bi-youtube me-1.5"></i> จัดการวิดีโอแนะนำ
</a>

<a href="{{ route('admin.calendar-events.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.calendar-events.*') ? 'active' : '' }}">
<i class="bi bi-calendar-event me-1.5"></i> จัดการปฏิทินกิจกรรม
</a>

<a href="{{ route('admin.modal-popups.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.modal-popups.*') ? 'active' : '' }}">
<i class="bi bi-megaphone me-1.5"></i> จัดการป๊อปอัปแจ้งเตือน
</a>
@endif

<!-- ข้อร้องเรียน / ข้อเสนอแนะ -->
@if(auth()->user()->hasPermission('view_feedbacks'))
<a href="{{ route('admin.feedbacks.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.feedbacks.*') ? 'active' : '' }}">
<i class="bi bi-chat-left-text me-1.5"></i> ข้อร้องเรียน / ข้อเสนอแนะ
</a>

<!-- 🌟 เพิ่มเมนูสรุปความพึงพอใจตรงนี้ -->
<a href="{{ route('admin.satisfactions.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.satisfactions.*') ? 'active' : '' }}">
    <i class="bi bi-bar-chart-fill me-1.5"></i> สรุปความพึงพอใจ
</a>
@endif
 
<!-- ประวัติผู้ขอดาวน์โหลด PDF -->
@if(auth()->user()->hasPermission('manage_contents'))
<a href="{{ route('admin.contents.download_logs') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.contents.download_logs') ? 'active' : '' }}">
<i class="bi bi-file-earmark-arrow-down-fill me-1.5"></i> ประวัติผู้ขอดาวน์โหลด PDF
</a>
@endif
@endif

<!-- 📌 SECTION: ความปลอดภัยและสมาชิก -->
@if(auth()->user()->hasPermission('manage_users') || auth()->user()->hasPermission('view_audit_logs') || auth()->user()->hasPermission('manage_roles'))
<hr class="text-secondary mx-3 my-2 opacity-25">
<div class="px-3 py-1 small text-uppercase text-muted fw-bold" style="font-size: 0.75rem; color: var(--theme-gold) !important;">
ความปลอดภัยและสมาชิก
</div>

<!-- บริหารจัดการสมาชิก -->
@if(auth()->user()->hasPermission('manage_users'))
<a href="{{ route('admin.users.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
<i class="bi bi-people me-1.5"></i> บริหารจัดการสมาชิก
</a>
@endif

<!-- ตรวจสอบสิทธิ์ & Audit Logs -->
@if(auth()->user()->hasPermission('view_audit_logs'))
<a href="{{ route('admin.access-control.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.access-control.*') ? 'active' : '' }}">
<i class="bi bi-shield-check me-1.5"></i> ตรวจสอบสิทธิ์ & Audit Logs
</a>
@endif

<!-- จัดการสิทธิ์การใช้งาน (RBAC) -->
@if(auth()->user()->hasPermission('manage_roles'))
<a href="{{ route('admin.roles.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
<i class="bi bi-shield-lock me-1.5"></i> จัดการสิทธิ์การใช้งาน (RBAC)
</a>
@endif

<!-- จัดการระบบ Offsite Backup (Google Drive OAuth 2.0) -->
@if(auth()->user()->hasPermission('manage_backup'))
<a href="{{ route('admin.settings.backup') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
<i class="bi bi-cloud-arrow-up-fill me-1.5"></i> จัดการระบบ Offsite Backup
</a>
@endif

@endif

<hr class="text-secondary mx-3 my-2 opacity-25">

<!-- ปุ่มลงชื่อออกจากระบบความปลอดภัย -->
<a href="#" class="list-group-item list-group-item-action text-warning fw-medium mb-3"
onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
<i class="bi bi-box-arrow-right me-1.5"></i> ลงชื่อออกจากระบบ
</a>
<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
@csrf
</form>
</div>
</div>

<!-- Main Content Presentation Area -->
<div id="page-content-wrapper" class="w-100 d-flex flex-column">
<!-- Sticky Header Topbar -->
<nav class="navbar navbar-expand-lg navbar-admin d-flex justify-content-between align-items-center shadow-sm">
<button class="btn btn-sm btn-toggle-sidebar px-3 py-2 fw-semibold" id="menu-toggle">
<i class="bi bi-list"></i> สลับเมนู
</button>

<div class="d-flex align-items-center">
<span class="me-3 small text-muted d-none d-md-inline">
ผู้ใช้งาน: <strong>{{ Auth::user()->name ?? 'Administrator' }}</strong>
</span>

@if(Auth::user()->is_admin || (method_exists(Auth::user(), 'hasRole') && Auth::user()->hasRole('super_admin')))
<span class="badge bg-danger px-2.5 py-1.5 fw-bold" style="font-size: 0.75rem;">Super Admin</span>
@else
<span class="badge bg-primary px-2.5 py-1.5 fw-bold" style="font-size: 0.75rem;">
{{ Auth::user()->roles->first()->name ?? 'Staff User' }}
</span>
@endif
</div>
</nav>

<!-- Dynamic Framework Rendering Yield Layer -->
<main class="container-fluid p-4 flex-grow-1">
@yield('admin_content')
</main>

<!-- Cultural Heritage Footer Accent -->
<footer class="cultural-footer py-3 text-center mt-auto">
<div class="container">
<span><i class="bi bi-globe"></i> โครงข่ายสถาปัตยกรรมข้อมูลทางวัฒนธรรม - สำนักศิลปะและวัฒนธรรม มหาวิทยาลัยราชภัฏเทพสตรี ลพบุรี</span>
</div>
</footer>
</div>
</div>

<!-- Local Enterprise Scripts Execution Engine -->
<script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('vendor/select2/js/select2.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('vendor/ckeditor5/ckeditor.js') }}"></script>
<script src="{{ asset('vendor/dropzone/dropzone.min.js') }}"></script>
<script src="{{ asset('vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>

<!-- Core Layout Controls -->
<script>
$(document).ready(function() {
// ควบคุมการยืด-หดของ Sidebar แบบนุ่มนวลผ่าน CSS Margins
$("#menu-toggle").click(function(e) {
e.preventDefault();
$("#wrapper").toggleClass("toggled");
});
});
</script>
@stack('admin_scripts')
</body>
</html>