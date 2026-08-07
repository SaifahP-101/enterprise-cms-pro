<!-- resources/views/layouts/app.blade.php -->
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!--  Optimizations: ช่องสำหรับรองรับ Dynamic SEO & Open Graph Tags ใน Phase 5 -->
    @yield('seo')

    <!--  Local Assets Injection (แทนที่ระบบ CDN เดิมเพื่อความปลอดภัยของระบบองค์กร) -->
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <!-- Local Typography & Fonts (Local Vendor) -->
    <link rel="stylesheet" href="{{ asset('vendor/fonts.css') }}">

    <style>
        :root {
            --font-sarabun: 'TH Sarabun New', 'THSarabunNew', 'Sarabun', sans-serif;
        }

        body {
            font-family: var(--font-sarabun);
            font-size: 14pt;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        main { flex: 1; }
        .dropdown-hover:hover > .dropdown-menu { display: block; }

        /* 📌 หัวข้อหลัก (Main Headings): 18pt */
        h1, h2, .h1, .h2, .heading-main, .main-title, .page-title {
            font-family: var(--font-sarabun);
            font-size: 18pt;
            font-weight: 700;
        }

        /* 📌 หัวข้อรอง (Sub Headings): 16pt */
        h3, h4, h5, h6, .h3, .h4, .h5, .h6, .heading-sub, .sub-title, .card-title, .navbar-brand, .nav-link, .btn {
            font-family: var(--font-sarabun);
            font-size: 16pt;
            font-weight: 600;
        }

        /* 📌 เนื้อหาทั่วไป (Body & Content): 14pt */
        p, span, div, li, a, td, th, label, input, textarea, select, button, .content-body {
            font-family: var(--font-sarabun);
        }
    </style>
</head>
<body>

    <!-- Dynamic Cached Navbar Menu (Phase 2) -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-success" href="{{ url('/') }}">Enterprise CMS Pro</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav ms-auto">
                    @foreach($globalMenus as $menu)
                        @if($menu->children->count() > 0)
                            <li class="nav-item dropdown dropdown-hover">
                                <a class="nav-link dropdown-toggle text-white" href="{{ $menu->url ? url($menu->url) : '#' }}">{{ $menu->title }}</a>
                                <ul class="dropdown-menu border-0 shadow-sm">
                                    @foreach($menu->children as $child)
                                        <li><a class="dropdown-item" href="{{ url($child->url) }}">{{ $child->title }}</a></li>
                                    @endforeach
                                </ul>
                            </li>
                        @else
                            <li class="nav-item"><a class="nav-link text-white" href="{{ $menu->url ? url($menu->url) : '#' }}">{{ $menu->title }}</a></li>
                        @endif
                    @endforeach
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Dynamic Interface -->
    <main class="container my-5">
        @yield('content')
    </main>

    <footer class="bg-dark text-white text-center py-3 mt-auto border-top border-secondary">
        <div class="container">
            <small>&copy; 2026 Enterprise CMS Pro. Local Self-Contained Assets Version. All Rights Reserved.</small>
        </div>
    </footer>

    <!--  Local JavaScript Execution Layer -->
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    @stack('scripts')
</body>
</html>