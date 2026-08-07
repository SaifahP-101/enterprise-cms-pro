@extends('layouts.frontend')

<!-- ========================================================================= -->
<!-- 1. SEO & PAGE META INJECTION -->
<!-- ========================================================================= -->
@section('seo')
    <title>{{ isset($category) ? $category->name : 'คลังสารสนเทศและกิจกรรม' }} | สำนักศิลปะและวัฒนธรรม มรภ.เทพสตรี</title>
    <meta name="description" content="รวบรวมข่าวสาร บทความวิชาการ จดหมายข่าว และคลังสิ่งพิมพ์ทางศิลปวัฒนธรรมท้องถิ่นลพบุรี">
    <meta name="keywords" content="ข่าวสาร, บทความ, งานวิจัย, จดหมายข่าว, ศิลปวัฒนธรรม, มรภ.เทพสตรี">
    
    <!-- Open Graph Metadata -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ isset($category) ? $category->name : 'คลังสารสนเทศและกิจกรรม' }} | สำนักศิลปะและวัฒนธรรม มรภ.เทพสตรี">
    <meta property="og:description" content="ศูนย์รวมข้อมูลสารสนเทศ วารสารวิจัย และกิจกรรมทำนุบำรุงศิลปวัฒนธรรม">
    <meta property="og:url" content="{{ url()->current() }}">

    <style>
        /* 📖 3D BOOK COVER & FLIP EFFECT FOR GRID INDEX (Aspect Ratio 2:3) */
        .book-card-container {
            perspective: 1000px;
            width: 100%;
            max-width: 210px;
            margin: 0 auto;
        }
        .book-card-3d {
            position: relative;
            width: 100%;
            aspect-ratio: 2 / 3;
            border-radius: 3px 12px 12px 3px;
            background-color: #FFFFFF;
            box-shadow: 3px 5px 15px rgba(0, 0, 0, 0.15), -2px 0 5px rgba(0, 0, 0, 0.08);
            transition: all 0.4s cubic-bezier(0.25, 1, 0.5, 1);
            transform-style: preserve-3d;
            overflow: hidden;
        }
        /* สันหนังสือ 3D (Book Spine Shadow) */
        .book-card-3d::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 12px;
            height: 100%;
            background: linear-gradient(to right, 
                        rgba(0, 0, 0, 0.32) 0%, 
                        rgba(255, 255, 255, 0.2) 35%, 
                        rgba(0, 0, 0, 0.15) 70%, 
                        rgba(0, 0, 0, 0.02) 100%);
            z-index: 10;
            border-radius: 3px 0 0 3px;
        }
        /* ขอบหน้ากระดาษหนังสือขวา (Pages Edge) */
        .book-card-3d::after {
            content: '';
            position: absolute;
            top: 3px;
            right: 0;
            width: 5px;
            height: calc(100% - 6px);
            background: repeating-linear-gradient(to bottom, #F8FAFC, #F8FAFC 2px, #E2E8F0 2px, #E2E8F0 4px);
            box-shadow: inset 1px 0 2px rgba(0, 0, 0, 0.12);
            border-radius: 0 2px 2px 0;
            z-index: 2;
        }
        /* Hover Effect สไตล์หนังสือยกเล่ม */
        .book-card-container:hover .book-card-3d {
            transform: rotateY(-15deg) rotateX(5deg) translateY(-6px);
            box-shadow: -14px 18px 26px rgba(0, 0, 0, 0.22), 0 0 15px rgba(212, 175, 55, 0.35);
        }
        .book-cover-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }
        .book-card-container:hover .book-cover-img {
            transform: scale(1.04);
        }

        /* 🖼️ STANDARD CARD STYLES */
        .standard-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .standard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(76, 29, 149, 0.12) !important;
        }
        .text-truncate-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* ==========================================================================
           ✨ ENTERPRISE PAGINATION CUSTOMIZATION (TRU PURPLE & GOLD THEME)
           ========================================================================== */
        .pagination-wrapper {
            margin-top: 50px;
        }

        /* 1. คอนเทนเนอร์หลัก จัดปุ่มกึ่งกลางพร้อมระยะห่าง */
        .pagination {
            justify-content: center;
            align-items: center;
            gap: 6px;
            margin-bottom: 0;
        }

        /* 2. สไตล์พื้นฐานของปุ่มกดตัวเลขและลูกศร */
        .pagination .page-link {
            color: var(--tru-purple);
            background-color: #FFFFFF;
            border: 1px solid #E9D5FF;
            border-radius: 10px !important;
            padding: 8px 16px;
            font-size: 0.92rem;
            font-weight: 600;
            font-family: 'Kanit', sans-serif;
            transition: all 0.25s ease-in-out;
            box-shadow: 0 2px 6px rgba(76, 29, 149, 0.03);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 42px;
            height: 42px;
        }

        /* 3. เอฟเฟกต์เมื่อวางเมาส์เหนือปุ่ม (Hover) */
        .pagination .page-link:hover {
            color: var(--tru-purple-dark);
            background-color: var(--tru-bg-lavender);
            border-color: var(--tru-purple);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(76, 29, 149, 0.12);
        }

        /* 4. เอฟเฟกต์เมื่อคลิก/Focus (ลบขอบฟ้าเดิมของเบราว์เซอร์) */
        .pagination .page-link:focus {
            box-shadow: 0 0 0 0.25rem rgba(76, 29, 149, 0.2);
            background-color: var(--tru-bg-lavender);
        }

        /* 5. สถานะปุ่มของหน้าที่กำลังเปิดอยู่ (Active State) */
        .pagination .page-item.active .page-link {
            color: #FFFFFF !important;
            background: linear-gradient(135deg, var(--tru-purple) 0%, var(--tru-purple-dark) 100%) !important;
            border-color: var(--tru-purple-dark) !important;
            box-shadow: 0 4px 14px rgba(76, 29, 149, 0.3) !important;
            pointer-events: none;
        }

        /* 6. ปุ่มที่กดไม่ได้ (Disabled State) */
        .pagination .page-item.disabled .page-link {
            color: #CBD5E1 !important;
            background-color: #F8FAFC !important;
            border-color: #E2E8F0 !important;
            opacity: 0.8;
            pointer-events: none;
            box-shadow: none;
        }

        /* 7. ควบคุมขนาด SVG/Icons ของลูกศร ย้อนกลับ / ถัดไป */
        .pagination .page-link svg {
            width: 1rem;
            height: 1rem;
            fill: currentColor;
        }

        body {
            --bs-bg-opacity: 1;
            background-color: rgba(var(--bs-light-rgb), var(--bs-bg-opacity)) !important;
        }
    </style>
@endsection

<!-- ========================================================================= -->
<!-- 2. MAIN CONTENT INDEX WORKSPACE -->
<!-- ========================================================================= -->
@section('content')
<div class="container my-5">
    
    <!-- Header Section -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-3 border-bottom gap-3">
        <div>
            <h2 class="fw-bold text-dark font-heading mb-1">
                <i class="bi bi-collection-fill text-warning me-2"></i>
                {{ isset($category) ? 'หมวดหมู่: ' . $category->name : 'คลังสารสนเทศและกิจกรรมองค์กร' }}
            </h2>
            <p class="text-muted small mb-0">สืบค้นข้อมูลข่าวสาร บทความวิชาการ วารสารวิจัย และสิ่งพิมพ์ทำนุบำรุงศิลปวัฒนธรรม</p>
        </div>
        <div>
            <a href="{{ route('home') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> กลับหน้าแรก
            </a>
        </div>
    </div>

    <!-- 🔍 FILTER & SEARCH BAR -->
    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white mb-5">
        <form action="{{ isset($category) ? route('contents.category', $category->slug) : route('contents.index') }}" method="GET" class="row g-2 align-items-center">
            
            <!-- Category Dropdown Filter -->
            <div class="col-md-5 col-lg-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-funnel-fill text-warning"></i></span>
                    <select id="categoryFilter" class="form-select border-0 bg-light" onchange="filterByCategory(this.value)">
                        <option value="">-- แสดงทุกหมวดหมู่สารสนเทศ --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->slug }}" {{ (isset($category) && $category->slug === $cat->slug) ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <!-- Search Keyword Input -->
            <div class="col-md-5 col-lg-6">
                <div class="input-group input-group-sm">
                    <input type="text" name="search" id="searchKeyword" class="form-control border-0 bg-light" placeholder="พิมพ์คำค้นหาหัวข้อข่าว/บทความ..." value="{{ request('search') }}">
                    <button class="btn btn-warning fw-bold text-dark px-3" type="submit">
                        <i class="bi bi-search me-1"></i> ค้นหา
                    </button>
                </div>
            </div>

            <!-- Clear Search / Filter Button -->
            @if(isset($category) || request('search'))
                <div class="col-md-2 col-lg-2 text-end">
                    <a href="{{ route('contents.index') }}" class="btn btn-sm btn-link text-danger text-decoration-none p-0 small">
                        <i class="bi bi-x-circle-fill me-1"></i> ล้างการค้นหาทั้งหมด
                    </a>
                </div>
            @endif
        </form>
    </div>

    <!-- 📖 🖼️ CONTENTS GRID CONTAINER -->
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">
        
        @php
            // รายชื่อ Category Slugs ที่ต้องเรนเดอร์เป็นรูปเล่มหนังสือ 3D (Book Cover Box Style)
            $bookCategorySlugs = [
                'research-and-articles',
                'books-and-journals',
                'newsletters',
                'educational-quality-assurance',
                'phra-narai-studies'
            ];
        @endphp

        @forelse($contents as $item)
            @php
                $isBookCategory = isset($item->category) && in_array($item->category->slug, $bookCategorySlugs);
            @endphp

            <div class="col">
                @if($isBookCategory)
                    <!-- ========================================================= -->
                    <!-- 📖 3D BOOK COVER BOX STYLE (สำหรับหนังสือ/วารสาร/วิจัย/จดหมายข่าว) -->
                    <!-- ========================================================= -->
                    <div class="card h-100 border-0 shadow-sm rounded-4 p-3 bg-white d-flex flex-column align-items-center text-center standard-card">
                        
                        <!-- Book Cover Display Area -->
                        <div class="book-card-container my-2">
                            <div class="book-card-3d">
                                <a href="{{ route('contents.show', $item->slug) }}" class="d-block w-100 h-100">
                                    <img src="{{ Str::startsWith($item->cover_image, ['http://', 'https://']) ? $item->cover_image : asset('storage/' . ($item->cover_image ?? 'contents/default-book.jpg')) }}" class="book-cover-img" alt="{{ $item->title }}">
                                </a>
                            </div>
                        </div>

                        <!-- Book Content Meta Area -->
                        <div class="card-body p-0 d-flex flex-column w-100 text-start mt-3">
                            <span class="badge bg-warning text-dark fw-bold mb-2 align-self-start px-2.5 py-1 rounded-pill small">
                                <i class="bi bi-journal-bookmark-fill me-1"></i> {{ $item->category->name }}
                            </span>
                            
                            <h6 class="fw-bold text-dark font-heading text-truncate-2 mb-2">
                                <a href="{{ route('contents.show', $item->slug) }}" class="text-dark text-decoration-none hover-purple">
                                    {{ $item->title }}
                                </a>
                            </h6>

                            <div class="mt-auto pt-2 border-top d-flex justify-content-between align-items-center text-muted small">
                                <span><i class="bi bi-eye-fill text-success me-1"></i> {{ number_format($item->view_count) }} ครั้ง</span>
                                <span><i class="bi bi-calendar3 me-1"></i> {{ $item->published_at ? $item->published_at->format('d/m/Y') : $item->created_at->format('d/m/Y') }}</span>
                            </div>
                        </div>

                    </div>
                @else
                    <!-- ========================================================= -->
                    <!-- 🖼️ STANDARD ARTICLE CARD STYLE (สำหรับข่าวสาร/กิจกรรมทั่วไป) -->
                    <!-- ========================================================= -->
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden bg-white d-flex flex-column standard-card">
                        
                        <!-- Standard Cover Area -->
                        <a href="{{ route('contents.show', $item->slug) }}" class="d-block overflow-hidden position-relative" style="height: 190px;">
                            <img src="{{ Str::startsWith($item->cover_image, ['http://', 'https://']) ? $item->cover_image : asset('storage/' . ($item->cover_image ?? 'contents/default-cover.jpg')) }}" class="w-100 h-100" style="object-fit: cover;" alt="{{ $item->title }}">
                            <span class="badge bg-dark bg-opacity-75 text-white fw-bold position-absolute top-0 start-0 m-3 px-3 py-1.5 rounded-pill backdrop-blur">
                                {{ $item->category->name }}
                            </span>
                        </a>

                        <!-- Standard Content Meta Area -->
                        <div class="card-body p-3.5 d-flex flex-column">
                            <h6 class="card-title fw-bold text-dark font-heading text-truncate-2 mb-2">
                                <a href="{{ route('contents.show', $item->slug) }}" class="text-dark text-decoration-none hover-purple">
                                    {{ $item->title }}
                                </a>
                            </h6>
                            
                            <p class="card-text text-muted small text-truncate-2 mb-3">
                                {{ Str::limit(strip_tags($item->body), 100) }}
                            </p>

                            <div class="mt-auto pt-2 border-top d-flex justify-content-between align-items-center text-muted small">
                                <span><i class="bi bi-calendar3 text-primary me-1"></i> {{ $item->published_at ? $item->published_at->format('d/m/Y') : $item->created_at->format('d/m/Y') }}</span>
                                <span><i class="bi bi-eye-fill text-success me-1"></i> {{ number_format($item->view_count) }}</span>
                            </div>
                        </div>

                    </div>
                @endif
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <i class="bi bi-inbox fs-1 text-muted opacity-50 d-block mb-2"></i>
                <h5 class="fw-bold text-secondary">ไม่พบรายการสารสนเทศในระบบ</h5>
                <p class="small text-muted mb-0">ขออภัย ไม่พบรายการข้อมูลบทความหรือสิ่งพิมพ์ตรงกับเงื่อนไขที่คุณระบุ</p>
            </div>
        @endforelse

    </div>

    <!-- 🥉 PAGINATION LINKS ENGINE -->
    @if($contents->hasPages())
        <div class="pagination-wrapper text-center">
            <!-- ⚡ [FIXED] ใช้ appends(request()->query()) เพื่อคงค่า Route (/category/{slug}) และ Query String (?search=...) ไว้เมื่อเปลี่ยนหน้า -->
            {{ $contents->appends(request()->query())->links('vendor.pagination.bootstrap-5') }}
            
            <div class="text-muted small mt-2 font-heading">
                แสดงหน้าที่ <span class="fw-bold text-dark">{{ $contents->currentPage() }}</span> 
                จากทั้งหมด <span class="fw-bold text-dark">{{ $contents->lastPage() }}</span> หน้า 
                (รวมสารสนเทศ {{ number_format($contents->total()) }} รายการ)
            </div>
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
    /**
     * ⚡ JavaScript Filter Helper Function:
     * จัดการการสลับหมวดหมู่โดยตรงผ่าน URL โดยยังคงคำค้นหา (search query) ไว้
     */
    function filterByCategory(selectedSlug) {
        const searchInput = document.getElementById('searchKeyword')?.value || '';
        let targetUrl = '';

        if (selectedSlug && selectedSlug.trim() !== '') {
            targetUrl = "{{ url('/category') }}/" + encodeURIComponent(selectedSlug);
        } else {
            targetUrl = "{{ route('contents.index') }}";
        }

        if (searchInput.trim() !== '') {
            targetUrl += '?search=' + encodeURIComponent(searchInput.trim());
        }

        window.location.href = targetUrl;
    }
</script>
@endpush