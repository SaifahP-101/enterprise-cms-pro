@extends('layouts.frontend')

@section('seo')
    <title>{{ $category->name }} | สำนักศิลปะและวัฒนธรรม มหาวิทยาลัยราชภัฏเทพสตรี</title>
    <meta name="description" content="รวบรวมข้อมูลสารสนเทศ ข่าวสาร และบทความในหมวดหมู่ {{ $category->name }} ดำเนินการโดย สำนักศิลปะและวัฒนธรรม มหาวิทยาลัยราชภัฏเทพสตรี">
    <meta property="og:title" content="{{ $category->name }} | สำนักศิลปะและวัฒนธรรม มหาวิทยาลัยราชภัฏเทพสตรี">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
@endsection

@push('styles')
<style>
    /* Section Typography & Titles */
    .section-tag {
        text-transform: uppercase;
        letter-spacing: 2px;
        font-size: 0.82rem;
        color: var(--tru-gold);
        font-weight: 700;
        display: block;
        margin-bottom: 6px;
    }
    .section-title {
        color: var(--tru-purple-dark);
        font-weight: 700;
        margin-bottom: 30px;
        position: relative;
        padding-bottom: 10px;
    }
    .section-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 50px;
        height: 4px;
        background-color: var(--tru-gold);
        border-radius: 2px;
    }

    /* Page Header Banner */
    .page-header {
        background: linear-gradient(135deg, var(--tru-purple-dark) 0%, #1E082A 100%);
        padding: 60px 0;
        margin-bottom: 40px;
        position: relative;
    }

    /* CMS Content Cards */
    .content-card {
        border: none;
        border-radius: 16px;
        background: #FFFFFF;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        transition: all 0.3s ease;
        height: 100%;
        border-bottom: 4px solid transparent;
    }
    .content-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.1);
        border-bottom-color: var(--tru-gold);
    }
    .content-badge {
        position: absolute;
        top: 14px;
        left: 14px;
        color: #FFFFFF;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        z-index: 2;
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

    /* 6. ปุ่มที่กดไม่ได้ (Disabled State เช่น อยู่หน้าแรกสุดแล้วกด ย้อนกลับ ไม่ได้) */
    .pagination .page-item.disabled .page-link {
        color: #CBD5E1 !important;
        background-color: #F8FAFC !important;
        border-color: #E2E8F0 !important;
        opacity: 0.8;
        pointer-events: none;
        box-shadow: none;
    }

    /* 7. ควบคุมขนาด SVG/Icons ของลูกศร ย้อนกลับ / ถัดไป ป้องกันไอคอนขยายใหญ่พัง */
    .pagination .page-link svg {
        width: 1rem;
        height: 1rem;
        fill: currentColor;
    }
</style>
@endpush

@section('content')

    <!-- 🥇 Page Header Banner -->
    <div class="page-header text-white">
        <div class="container position-relative z-1">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-white-50 text-decoration-none"><i class="bi bi-house-door-fill"></i> หน้าหลัก</a></li>
                    <li class="breadcrumb-item active text-white fw-bold" aria-current="page">{{ $category->name }}</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold font-heading mb-2">
                หมวดหมู่: <span style="color: var(--tru-gold);">{{ $category->name }}</span>
            </h1>
            <p class="lead opacity-75 mb-0" style="max-width: 700px; font-size: 1.05rem;">
                รวบรวมรายการข้อมูล ข่าวสาร และสารสนเทศภูมิปัญญาที่เกี่ยวข้องทั้งหมดในหมวดหมู่นี้
            </p>
        </div>
    </div>

    <!-- 🥈 Content List Grid -->
    <section class="container py-4 mb-5">
        
        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
            <h5 class="fw-bold text-dark font-heading mb-0">
                <i class="bi bi-collection-fill text-warning me-2"></i> รายการข้อมูลทั้งหมด ({{ $contents->total() }} รายการ)
            </h5>
        </div>

        <div class="row g-4">
            @forelse($contents as $content)
                <div class="col-md-6 col-lg-4 col-xl-3">
                    <!-- ⚡ position-relative สำหรับรองรับ stretched-link -->
                    <div class="content-card position-relative d-flex flex-column">
                        <!-- Functional Color Code Badge -->
                        <span class="content-badge shadow-sm" style="background-color: {{ $category->color_code ?? 'var(--tru-purple-dark)' }};">
                            {{ $category->name }}
                        </span>
                        
                        <!-- Image Ratio Box -->
                        <div class="ratio ratio-16x9 overflow-hidden bg-light">
                            @if($content->cover_image)
                                <img src="{{ Str::startsWith($content->cover_image, ['http://', 'https://']) ? $content->cover_image : asset('storage/'.$content->cover_image) }}" class="w-100 h-100 object-fit-cover" alt="{{ $content->title }}">
                            @else
                                <!-- Fallback Image ถ้าไม่มีรูปปก -->
                                <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-secondary bg-opacity-10 text-muted">
                                    <i class="bi bi-image fs-1 opacity-50"></i>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Content Data -->
                        <div class="p-4 d-flex flex-column flex-grow-1">
                            <div class="text-muted small mb-2 d-flex align-items-center gap-2">
                                <span><i class="bi bi-calendar3 me-1"></i> {{ $content->published_at ? $content->published_at->format('d/m/Y') : $content->created_at->format('d/m/Y') }}</span>
                                <span>•</span>
                                <span><i class="bi bi-eye me-1"></i> {{ number_format($content->view_count) }} ครั้ง</span>
                            </div>
                            
                            <h5 class="fw-bold text-dark font-heading fs-6 mb-3 text-truncate-2">
                                <!-- ⚡ Stretched-link ทำให้คลิกเข้าอ่านได้ทั้งการ์ด -->
                                <a href="{{ route('contents.show', $content->slug) }}" class="text-dark text-decoration-none stretched-link">
                                    {{ $content->title }}
                                </a>
                            </h5>
                            
                            <p class="text-muted small mb-0 mt-auto text-truncate-2" style="line-height: 1.6;">
                                {{ Str::limit(strip_tags($content->body), 90) }}
                            </p>
                        </div>
                    </div>
                </div>
            @empty
                <!-- Empty State Block -->
                <div class="col-12 text-center py-5">
                    <div class="p-5 border border-dashed rounded-4 bg-light">
                        <i class="bi bi-inbox fs-1 text-muted opacity-50 d-block mb-3"></i>
                        <h5 class="fw-bold text-dark font-heading">ยังไม่มีข้อมูลในหมวดหมู่นี้</h5>
                        <p class="text-muted small mb-0">ขออภัย ขณะนี้ยังไม่มีการเผยแพร่ข้อมูลหรือข่าวสารในหมวดหมู่ "{{ $category->name }}"</p>
                        <a href="{{ url('/') }}" class="btn btn-outline-primary mt-4 rounded-pill px-4" style="color: var(--tru-purple); border-color: var(--tru-purple);">
                            ← กลับสู่หน้าหลัก
                        </a>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- 🥉 Pagination Links -->
        @if($contents->hasPages())
            <div class="pagination-wrapper text-center">
                <!-- เรียกใช้ Custom View ที่เราสร้างไว้ -->
                {{ $contents->links('vendor.pagination.bootstrap-5') }}
                
                <div class="text-muted small mt-2 font-heading">
                    แสดงหน้าที่ <span class="fw-bold text-dark">{{ $contents->currentPage() }}</span> 
                    จากทั้งหมด <span class="fw-bold text-dark">{{ $contents->lastPage() }}</span> หน้า 
                    (รวมสารสนเทศ {{ number_format($contents->total()) }} รายการ)
                </div>
            </div>
        @endif

    </section>

@endsection