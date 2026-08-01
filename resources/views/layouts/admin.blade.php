<!-- resources/views/layouts/admin.blade.php -->
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ระบบบริหารจัดการ') | สำนักศิลปะและวัฒนธรรม มรภ.เทพสตรี</title>

    <!--  Core Typography Fonts Integration -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;500;600;700&family=Prompt:wght@300;400;500;600&family=Sriracha&display=swap" rel="stylesheet">

    <!--  Local Enterprise Core Stylesheets -->
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/select2/css/select2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/datatables/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/dropzone/dropzone.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet">

    <!--  Custom Visual Identity Theme (Thai Contemporary & Elegant Style) -->
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

        h1, h2, h3, h4, h5, h6, .sys-title {
            font-family: var(--font-header);
            font-weight: 600;
        }

        .cultural-heading {
            font-family: 'Sriracha', cursive;
            color: var(--theme-gold);
        }

        /* <i class="bi bi-shield-check"></i> Sidebar Architecture Styling */
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

        /* <i class="bi bi-shield-check"></i> Top Navbar Architecture Styling */
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>

    <div class="d-flex" id="wrapper">
        <!--  Sidebar Layout Component -->
        <div id="sidebar-wrapper" class="shadow">
            <div class="sidebar-heading text-center">
                <div class="mb-2">
                    <span class="fs-4 text-white d-block fw-bold tracking-wide">สำนักศิลปะฯ</span>
                    <span class="small d-block text-muted" style="color: var(--theme-gold) !important;">มรภ.เทพสตรี</span>
                </div>
            </div>
            
            <div class="list-group list-group-flush mt-3 style-scrollbar" style="max-height: calc(100vh - 120px); overflow-y: auto;">
                <!--  แผงควบคุมหลัก -->
                <a href="{{ route('admin.dashboard.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.dashboard.index') ? 'active' : '' }}"><i class="bi bi-speedometer2"></i> หน้าแรกบอร์ดควบคุม</a>
                
                <!--  จัดการระบบเมนูไดนามิก -->
                <a href="{{ route('admin.menus.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.menus.*') ? 'active' : '' }}"><i class="bi bi-list-nested"></i> จัดการเมนูอัจฉริยะ</a>
                
                <!--  จัดการหมวดหมู่เนื้อหา -->
                <a href="{{ route('admin.categories.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}"><i class="bi bi-folder2-open"></i> จัดการหมวดหมู่เนื้อหา</a>
                
                <!--  ระบบบริหารจัดการบทความ / ถังขยะกู้คืนข้อมูล -->
                <a href="{{ route('admin.contents.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.contents.*') && !request()->routeIs('admin.contents.trash') ? 'active' : '' }}"><i class="bi bi-pencil-square"></i> ระบบเขียนข่าวและกิจกรรม</a>
                
                <!--  จัดการหน้าเพจ -->
                <a href="{{ route('admin.pages.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.pages.index') ? 'active' : '' }} "><i class="bi bi-folder2-open"></i> จัดการหน้าเพจ</a>

                <a href="{{ route('admin.contents.trash') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.contents.trash') ? 'active' : '' }} text-info"><i class="bi bi-trash3"></i> ถังขยะกู้คืนบทความ</a>
                
                <hr class="text-secondary mx-3 my-2 opacity-25">
                <div class="px-3 py-1 small text-uppercase text-muted fw-bold" style="font-size: 0.75rem; color: var(--theme-gold) !important;">Component หน้าแรก</div>
                
                <!--  สไลด์แบนเนอร์ & ป๊อปอัปควบคุมเวลา -->
                <a href="{{ route('admin.slideshows.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.slideshows.*') ? 'active' : '' }}"><i class="bi bi-images"></i> จัดการแบนเนอร์หน้าแรก</a>
                
                <!-- ⚡ เพิ่มเมนูจัดการวิดีโอแนะนำตรงนี้ ⚡ -->
                <a href="{{ route('admin.featured-videos.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.featured-videos.*') ? 'active' : '' }}"><i class="bi bi-youtube"></i> จัดการวิดีโอแนะนำ</a>
                
                <!-- ⚡ เพิ่มเมนูจัดการปฏิทินกิจกรรม ⚡ -->
                <a href="{{ route('admin.calendar-events.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.calendar-events.*') ? 'active' : '' }}"><i class="bi bi-calendar-event"></i> จัดการปฏิทินกิจกรรม</a>

                <a href="{{ route('admin.modal-popups.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.modal-popups.*') ? 'active' : '' }}"><i class="bi bi-megaphone"></i> จัดการป๊อปอัปแจ้งเตือน</a>
                
                <!-- ⚡ เพิ่มเมนูจัดการข้อร้องเรียนและข้อเสนอแนะตรงนี้ ⚡ -->
                <a href="{{ route('admin.feedbacks.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.feedbacks.*') ? 'active' : '' }}">
                    <i class="bi bi-chat-left-text"></i> ข้อร้องเรียน / ข้อเสนอแนะ
                </a>
                
                <hr class="text-secondary mx-3 my-2 opacity-25">
                <div class="px-3 py-1 small text-uppercase text-muted fw-bold" style="font-size: 0.75rem; color: var(--theme-gold) !important;">ความปลอดภัยและสมาชิก</div>
                
                <!--  บริหารจัดการสมาชิกและสิทธิ์การตรวจสอบย้อนหลัง -->
                <a href="{{ route('admin.users.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"><i class="bi bi-people"></i> บริหารจัดการสมาชิก</a>
                <a href="{{ route('admin.access-control.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.access-control.*') ? 'active' : '' }}"><i class="bi bi-shield-check"></i> ตรวจสอบสิทธิ์ & Audit Logs</a>
                
                <hr class="text-secondary mx-3 my-2">
                
                <!-- ปุ่มลงชื่อออกจากระบบความปลอดภัย -->
                <a href="#" class="list-group-item list-group-item-action text-warning fw-medium" 
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="bi bi-box-arrow-right"></i> ลงชื่อออกจากระบบ
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
        </div>

        <!--  Main Content Presentation Area -->
        <div id="page-content-wrapper" class="w-100 d-flex flex-column">
            <!-- Sticky Header Topbar -->
            <nav class="navbar navbar-expand-lg navbar-admin d-flex justify-content-between align-items-center shadow-sm">
                <button class="btn btn-sm btn-toggle-sidebar px-3 py-2 fw-semibold" id="menu-toggle"><i class="bi bi-list"></i> สลับเมนู</button>
                
                <div class="d-flex align-items-center">
                    <span class="me-3 small text-muted d-none d-md-inline">ผู้ใช้งาน: <strong>{{ Auth::user()->name ?? 'Administrator' }}</strong></span>
                    <span class="badge bg-danger px-2.5 py-1.5 fw-bold" style="font-size: 0.75rem;">สิทธิ์ระบบหลังบ้าน</span>
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

    <!--  Local Enterprise Scripts Execution Engine -->
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
            //  FIX: ควบคุมการยืด-หดของ Sidebar แบบนุ่มนวลผ่าน CSS Margins แทนการยัด d-none ดิบๆ
            $("#menu-toggle").click(function(e) {
                e.preventDefault();
                $("#wrapper").toggleClass("toggled");
            });
        });
    </script>
    @stack('admin_scripts')
</body>
</html>