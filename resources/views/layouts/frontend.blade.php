<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- 1. DYNAMIC SEO & OPEN GRAPH INJECTION -->
    @yield('seo')

    <!-- 2. CORE CSS STYLESHEETS & ICONS -->
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons (100% Vector Icons) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- 3. CORE TYPOGRAPHY & FONTS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- 4. VISUAL IDENTITY THEME (TRU PURPLE & GOLD SYSTEM) -->
    <style>
        :root {
            /* Palette 60-30-10 System */
            --tru-purple: #4C1D95;
            --tru-purple-dark: #3F1551;
            --tru-gold: #D4AF37;
            --tru-gold-hover: #F59E0B;
            --tru-bg-lavender: #F3E8FF;
            --tru-slate-dark: #1E293B;
            --tru-slate-light: #F8FAFC;
        }

        body {
            font-family: 'Sarabun', sans-serif;
            color: var(--tru-slate-dark);
            background-color: #FFFFFF;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        h1, h2, h3, h4, h5, h6, .font-heading, .navbar-brand, .nav-link, .btn {
            font-family: 'Kanit', sans-serif;
        }

        /* Top Bar Header */
        .top-bar {
            background: linear-gradient(135deg, var(--tru-purple-dark) 0%, #1E082A 100%);
            color: #FFF;
            font-size: 0.85rem;
            padding: 8px 0;
            border-bottom: 2px solid var(--tru-gold);
        }
        .top-bar a {
            color: #E9D5FF;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .top-bar a:hover {
            color: var(--tru-gold);
        }

        /* Navbar Header & Dynamic Dropdown Styling */
        .navbar-tru {
            background-color: rgba(255, 255, 255, 0.98);
            box-shadow: 0 4px 20px rgba(76, 29, 149, 0.08);
            padding: 10px 0;
        }
        .nav-link {
            color: var(--tru-slate-dark) !important;
            font-weight: 500;
            padding: 8px 16px !important;
            transition: all 0.2s ease;
        }
        .nav-link:hover, .nav-link.active {
            color: var(--tru-purple) !important;
        }
        
        /* Dropdown Styling for Submenus */
        .navbar-tru .dropdown-menu {
            border: none;
            border-top: 3px solid var(--tru-gold);
            border-radius: 0 0 10px 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            padding: 8px 0;
            margin-top: 0;
            min-width: 220px;
        }
        .navbar-tru .dropdown-item {
            font-size: 0.92rem;
            font-weight: 500;
            color: var(--tru-slate-dark);
            padding: 8px 20px;
            transition: all 0.2s ease;
        }
        .navbar-tru .dropdown-item:hover {
            color: var(--tru-purple);
            background-color: var(--tru-bg-lavender);
            padding-left: 24px;
        }

        /* Desktop Hover Effect for Dropdowns */
        @media (min-width: 992px) {
            .navbar-tru .nav-item.dropdown:hover .dropdown-menu {
                display: block;
                animation: fadeInDown 0.2s ease forwards;
            }
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .btn-tru-gold {
            background: linear-gradient(135deg, var(--tru-gold) 0%, #B45309 100%);
            color: #FFFFFF !important;
            font-weight: 600;
            border-radius: 30px;
            padding: 8px 24px !important;
            border: none;
            box-shadow: 0 4px 12px rgba(212, 175, 55, 0.3);
            transition: all 0.2s ease;
        }
        .btn-tru-gold:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(212, 175, 55, 0.5);
        }

        /* Footer Accent */
        footer {
            background-color: #0F172A;
            color: #94A3B8;
            border-top: 5px solid var(--tru-purple);
            font-size: 0.9rem;
            margin-top: auto;
        }
        .footer-heading {
            color: #FFFFFF;
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 8px;
        }
        .footer-heading::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 3px;
            background-color: var(--tru-gold);
            border-radius: 2px;
        }
        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .footer-links li {
            margin-bottom: 12px;
        }
        .footer-links a {
            color: #CBD5E1;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .footer-links a:hover {
            color: var(--tru-gold);
            padding-left: 4px;
        } 
    </style>

    @stack('styles')
</head>
<body>

    <!-- 📞 Top Utility Bar (ข้อมูล มรภ.เทพสตรี) -->
    <div class="top-bar d-none d-lg-block">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-flex gap-4">
                <span><i class="bi bi-telephone-fill text-white me-1"></i> 036 - 413096</span>
                <span><i class="bi bi-geo-alt-fill text-white me-1"></i> 321 ต.ทะเลชุบศร อ.เมือง จ.ลพบุรี 15000</span>
                <span><i class="bi bi-clock-fill text-white me-1"></i> 08:30 - 16:30 น. (จันทร์ - ศุกร์)</span>
            </div>
            <div class="d-flex gap-3 align-items-center">
                <a href="https://facebook.com/artcultureTRU99" target="_blank" title="Facebook Page"><i class="bi bi-facebook fs-6"></i></a>
                <a href="mailto:thepsatriculture@lawasri.tru.ac.th" title="Send Email"><i class="bi bi-envelope-fill fs-6"></i></a>
                <span class="opacity-25">|</span>
                <span class="fw-bold text-white">TH</span>
            </div>
        </div>
    </div>

    <!-- 🏛️ Navigation Header ( id="mainNavbar" Dynamic Multi-Level Navigation ) -->
    <nav class="navbar navbar-expand-lg sticky-top navbar-tru">
        <div class="container">
            <!-- Brand Logo & Title -->
            <a class="navbar-brand d-flex align-items-center gap-2.5" href="{{ url('/') }}">
                <img src="{{ asset('assets/images/logo-tru-culture.png') }}" alt="สำนักศิลปะและวัฒนธรรม มรภ.เทพสตรี" class="me-2" style="max-height: 52px;">
                <div>
                    <div class="fs-6 fw-bold text-dark lh-1">สำนักศิลปะและวัฒนธรรม</div>
                    <div style="font-size: 0.75rem; color: var(--tru-purple);" class="fw-semibold">มหาวิทยาลัยราชภัฏเทพสตรี</div>
                </div>
            </a>

            <!-- Mobile Navbar Toggle Button -->
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Dynamic Menu Items Container -->
            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center">
                    
                    {{-- 🔄 วนลูปเรนเดอร์เมนูจากตัวแปรแคช $navigationTree --}}
                    @forelse($navigationTree ?? [] as $menu)
                        
                        {{-- 📌 กรณีที่ 1: เมนูที่มีเมนูย่อย (Parent Menu with Submenus) --}}
                        @if($menu->children && $menu->children->isNotEmpty())
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown{{ $menu->id }}" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    {{ $menu->title }}
                                </a>
                                <ul class="dropdown-menu border-0 shadow-sm rounded-3" aria-labelledby="navbarDropdown{{ $menu->id }}">
                                    @foreach($menu->children as $child)
                                        @php
                                            $childUrl = \Illuminate\Support\Str::startsWith($child->url, ['http://', 'https://']) 
                                                ? $child->url 
                                                : url($child->url ?? '#');
                                        @endphp
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center justify-content-between" href="{{ $childUrl }}">
                                                <span>{{ $child->title }}</span>
                                                <i class="bi bi-chevron-right opacity-25" style="font-size: 0.7rem;"></i>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </li>

                        {{-- 📌 กรณีที่ 2: เมนูชั้นเดียว (Single Root Menu Link) --}}
                        @else
                            @php
                                $menuUrl = \Illuminate\Support\Str::startsWith($menu->url, ['http://', 'https://']) 
                                    ? $menu->url 
                                    : url($menu->url ?? '#');
                            @endphp
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is(ltrim($menu->url ?? '', '/')) ? 'active' : '' }}" href="{{ $menuUrl }}">
                                    {{ $menu->title }}
                                </a>
                            </li>
                        @endif

                    @empty
                        {{-- Fallback กรณีฐานข้อมูลยังไม่มีรายการเมนู --}}
                        <li class="nav-item"><a class="nav-link active" href="{{ url('/') }}">หน้าแรก</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('/contact') }}">ติดต่อเรา</a></li>
                    @endforelse
 
                </ul>
            </div>
        </div>
    </nav>

    <!-- ⚡ Dynamic Main Content Render Workspace -->
    <main class="flex-grow-1">
        @yield('content')
    </main>

    <!-- 🏢 11. COMPREHENSIVE FOOTER -->
    <footer class="pt-5 pb-3">
        <div class="container">
            <div class="row g-4 mb-4">
                <!-- คอลัมน์ที่ 1: ติดต่อเรา -->
                <div class="col-lg-4">
                    <h5 class="footer-heading">ติดต่อเรา</h5>
                    <p class="fw-bold text-white mb-2">สำนักศิลปะและวัฒนธรรม มหาวิทยาลัยราชภัฏเทพสตรี</p>
                    <ul class="footer-links">
                        <li class="d-flex gap-2"><i class="bi bi-geo-alt-fill text-warning"></i> 321 ตำบลทะเลชุบศร อำเภอเมือง จังหวัดลพบุรี 15000</li>
                        <li class="d-flex gap-2"><i class="bi bi-telephone-fill text-warning"></i> 036 - 413096</li>
                        <li class="d-flex gap-2"><i class="bi bi-envelope-fill text-warning"></i> thepsatriculture@lawasri.tru.ac.th</li>
                        <li class="d-flex gap-2"><i class="bi bi-facebook text-warning"></i> Facebook: artcultureTRU99</li>
                    </ul>
                </div>

                <!-- คอลัมน์ที่ 2: หน่วยงานภายนอก -->
                <div class="col-sm-6 col-lg-4">
                    <h5 class="footer-heading">หน่วยงานภายนอก</h5>
                    <ul class="footer-links">
                        <li><a href="https://www.m-culture.go.th/lopburi/" target="_blank"><i class="bi bi-chevron-right text-warning me-1.5"></i> สำนักงานวัฒนธรรมจังหวัดลพบุรี</a></li>
                        <li><a href="https://www.mhesi.go.th/" target="_blank"><i class="bi bi-chevron-right text-warning me-1.5"></i> กระทรวงการอุดมศึกษา วิทยาศาสตร์ วิจัยและนวัตกรรม</a></li>
                        <li><a href="#" target="_blank"><i class="bi bi-chevron-right text-warning me-1.5"></i> สภาศิลปะและวัฒนธรรม มหาวิทยาลัยราชภัฏแห่งประเทศไทย</a></li>
                    </ul>
                </div>

                <!-- คอลัมน์ที่ 3: เว็บไซต์ที่เกี่ยวข้อง -->
                <div class="col-sm-6 col-lg-4">
                    <h5 class="footer-heading">เว็บไซต์ที่เกี่ยวข้อง</h5>
                    <ul class="footer-links">
                        <li><a href="https://www.lopburi.go.th/" target="_blank"><i class="bi bi-link-45deg text-warning me-1.5"></i> จังหวัดลพบุรี</a></li>
                        <li><a href="https://www.tru.ac.th/" target="_blank"><i class="bi bi-link-45deg text-warning me-1.5"></i> มหาวิทยาลัยราชภัฏเทพสตรี</a></li>
                        <li><a href="#" target="_blank"><i class="bi bi-link-45deg text-warning me-1.5"></i> สถานที่ท่องเที่ยวเชิงวัฒนธรรม จังหวัดลพบุรี</a></li>
                    </ul>
                </div>
            </div>

            <!-- Copyright -->
            <div class="pt-4 border-top border-secondary border-opacity-25 text-center text-xs opacity-75">
                <p class="mb-0">&copy; 2026 สำนักศิลปะและวัฒนธรรม มหาวิทยาลัยราชภัฏเทพสตรี. สงวนลิขสิทธิ์ทั้งหมด.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>