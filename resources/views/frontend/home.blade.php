@extends('layouts.frontend')

@section('seo')
    <title>หน้าแรก | สำนักศิลปะและวัฒนธรรม มหาวิทยาลัยราชภัฏเทพสตรี</title>
    <meta name="description" content="สำนักศิลปะและวัฒนธรรม มหาวิทยาลัยราชภัฏเทพสตรี เป็นหน่วยงานสนับสนุนการผลิตบัณฑิต โดยจัดกิจกรรมทำนุบำรุงศิลปวัฒนธรรมอย่างต่อเนื่อง">
    <meta property="og:title" content="สำนักศิลปะและวัฒนธรรม มหาวิทยาลัยราชภัฏเทพสตรี">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
@endsection

@push('styles')
<style>
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
    .section-title.center::after {
        left: 50%;
        transform: translateX(-50%);
    }

    /* Missions Dashboard (5 Domains Grid) */
    .dashboard-section {
        background-color: var(--tru-bg-lavender);
        padding: 60px 0;
        border-radius: 30px;
        margin: 40px 0;
    }
    .mission-card {
        background: #FFFFFF;
        border-radius: 16px;
        padding: 24px 16px;
        text-align: center;
        box-shadow: 0 4px 20px rgba(76, 29, 149, 0.05);
        border: 1px solid #E9D5FF;
        transition: all 0.3s ease;
        height: 100%;
        position: relative;
    }
    .mission-card:hover {
        transform: translateY(-6px);
        border-color: var(--tru-gold);
        box-shadow: 0 10px 25px rgba(212, 175, 55, 0.2);
    }
    .mission-icon {
        width: 60px;
        height: 60px;
        background-color: rgba(76, 29, 149, 0.08);
        color: var(--tru-purple);
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 14px;
        transition: all 0.3s ease;
    }
    .mission-card:hover .mission-icon {
        background-color: var(--tru-purple);
        color: #FFFFFF;
    }

    /* CMS News Cards */
    .news-card {
        border: none;
        border-radius: 16px;
        background: #FFFFFF;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        transition: all 0.3s ease;
        height: 100%;
    }
    .news-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.1);
    }
    .news-badge {
        position: absolute;
        top: 14px;
        left: 14px;
        background-color: var(--tru-purple-dark);
        color: var(--tru-gold);
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        z-index: 2;
    }

    /* 📖 3D BOOK COVER & FLIP EFFECT */
    .book-card-container {
        perspective: 1200px;
        height: 100%;
    }
    .book-card-3d {
        position: relative;
        width: 100%;
        aspect-ratio: 2 / 3;
        border-radius: 4px 14px 14px 4px;
        background-color: #FFFFFF;
        box-shadow: 4px 6px 18px rgba(0, 0, 0, 0.12), -2px 0 6px rgba(0, 0, 0, 0.08);
        transition: all 0.45s cubic-bezier(0.25, 1, 0.5, 1);
        transform-style: preserve-3d;
        overflow: hidden;
    }
    .book-card-3d::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 14px;
        height: 100%;
        background: linear-gradient(to right, rgba(0, 0, 0, 0.32) 0%, rgba(255, 255, 255, 0.2) 35%, rgba(0, 0, 0, 0.15) 70%, rgba(0, 0, 0, 0.02) 100%);
        z-index: 10;
        border-radius: 4px 0 0 4px;
    }
    .book-card-3d::after {
        content: '';
        position: absolute;
        top: 4px;
        right: 0;
        width: 6px;
        height: calc(100% - 8px);
        background: repeating-linear-gradient(to bottom, #F8FAFC, #F8FAFC 2px, #E2E8F0 2px, #E2E8F0 4px);
        box-shadow: inset 1px 0 2px rgba(0, 0, 0, 0.15);
        border-radius: 0 2px 2px 0;
        z-index: 2;
    }
    .book-card-container:hover .book-card-3d {
        transform: rotateY(-18deg) rotateX(6deg) translateY(-8px);
        box-shadow: -18px 22px 32px rgba(0, 0, 0, 0.25), 0 0 18px rgba(212, 175, 55, 0.3);
    }
    .book-cover-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    .book-card-container:hover .book-cover-img {
        transform: scale(1.04);
    }
    .book-overlay-info {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 24px 16px 16px 20px;
        background: linear-gradient(to top, rgba(30, 8, 42, 0.95) 0%, rgba(30, 8, 42, 0.75) 60%, rgba(0, 0, 0, 0) 100%);
        color: #FFFFFF;
        z-index: 11;
        transition: all 0.3s ease;
    }
    .book-card-container:hover .book-overlay-info {
        padding-bottom: 20px;
        background: linear-gradient(to top, rgba(76, 29, 149, 0.98) 0%, rgba(30, 8, 42, 0.82) 70%, rgba(0, 0, 0, 0) 100%);
    }

    /* Calendar Grid */
    .calendar-widget {
        background-color: #FFFFFF;
        border-radius: 20px;
        box-shadow: 0 4px 20px rgba(76, 29, 149, 0.06);
        padding: 25px;
        border: 1px solid #E9D5FF;
    }
    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 8px;
        text-align: center;
    }
    .calendar-day-label {
        font-weight: 600;
        color: var(--tru-purple);
        font-size: 0.8rem;
    }
    .calendar-day {
        padding: 8px 0;
        border-radius: 8px;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.2s;
    }
    .calendar-day.active {
        background-color: var(--tru-purple);
        color: #FFFFFF;
        font-weight: 600;
    }

    /* Portal Bar */
    .portal-bar {
        background: linear-gradient(135deg, var(--tru-purple-dark) 0%, #1E082A 100%);
        color: #FFFFFF;
        border-radius: 24px;
        padding: 40px;
        margin: 40px 0;
    }
    .portal-btn {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: #FFFFFF;
        padding: 15px 25px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        gap: 15px;
        text-decoration: none;
        transition: all 0.3s;
        cursor: pointer;
    }
    .portal-btn:hover {
        background: #FFFFFF;
        color: var(--tru-purple-dark);
        transform: translateY(-4px);
    }

    /* ⚡ Mobile Swipeable Cards (Horizontal Scroll สำหรับมุมมองมือถือ) ⚡ */
    @media (max-width: 767.98px) {
        .swipeable-row-mobile {
            flex-wrap: nowrap !important;
            overflow-x: auto !important;
            overflow-y: hidden !important;
            -webkit-overflow-scrolling: touch;
            scroll-snap-type: x mandatory;
            padding-bottom: 1.5rem; /* เพิ่มพื้นที่สำหรับ Scrollbar */
        }
        /* กำหนดขนาดของการ์ดในมือถือให้โผล่ขอบชิ้นต่อไป เพื่อบอกให้รู้ว่าเลื่อนได้ */
        .swipeable-row-mobile > [class*="col-"] {
            flex: 0 0 85% !important;
            max-width: 85% !important;
            scroll-snap-align: start;
        }
        
        /* ตกแต่ง Scrollbar ให้ดูโมเดิร์น */
        .swipeable-row-mobile::-webkit-scrollbar {
            height: 6px;
        }
        .swipeable-row-mobile::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
            border-radius: 10px;
        }
        .swipeable-row-mobile::-webkit-scrollbar-thumb {
            background: var(--tru-gold, #D4AF37);
            border-radius: 10px;
        }
    }
</style>
@endpush

@section('content')

    <!-- Notification Alert หลังจากผู้ใช้ส่งข้อร้องเรียนสำเร็จ -->
    @if(session('feedback_success'))
        <div class="container mt-4">
            <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-4 p-4 text-center border-0" role="alert" style="background-color: #D1E7DD; color: #0F5132;">
                <i class="bi bi-check-circle-fill fs-3 text-success d-block mb-2"></i>
                <h5 class="fw-bold font-heading mb-1">ส่งข้อมูลสำเร็จเรียบร้อยแล้ว</h5>
                <p class="mb-0">{{ session('feedback_success') }}</p>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    <!-- 1. SLIDE SHOW -->
    <div id="mainCarousel" class="carousel slide position-relative overflow-hidden" data-bs-ride="carousel">
        <div class="carousel-inner">
            @forelse($slideshows ?? [] as $key => $slide)
                <div class="carousel-item {{ $key == 0 ? 'active' : '' }}" style="height: 500px; background: url('{{ asset('storage/'.$slide->image_path) }}') center/cover no-repeat;">
                    <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center" style="background: linear-gradient(131deg, rgb(0 0 0 / 74%) 0%, rgb(0 0 0 / 21%) 71%);">
                        <div class="container text-white text-center">
                            <h1 class="display-5 fw-bold font-heading my-4" style="color: #FFF !important; font-size: 3rem !important;">{{ $slide->title }}</h1>
                            <p class="lead opacity-90 mb-4">{{ $slide->subtitle }}</p>
                            @if($slide->link_url)
                                <a href="{{ $slide->link_url }}" class="btn btn-tru-gold px-4 py-2">อ่านรายละเอียด</a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="carousel-item active" style="height: 480px; background: linear-gradient(135deg, var(--tru-purple-dark) 0%, #1E082A 100%);">
                    <div class="container h-100 d-flex align-items-center text-white">
                        <div>
                            <span class="badge bg-warning text-dark mb-3 px-3 py-2 rounded-pill font-heading">
                                <i class="bi bi-bank me-1"></i> ทำนุบำรุงศิลปวัฒนธรรมเมืองลพบุรี
                            </span>
                            <h1 class="display-5 fw-bold mb-3 font-heading">
                                สำนักศิลปะและวัฒนธรรม <br>
                                <span style="color: var(--tru-gold);">มหาวิทยาลัยราชภัฏเทพสตรี</span>
                            </h1>
                            <p class="lead opacity-75 mb-4" style="max-width: 700px;">
                                สำนักศิลปะและวัฒนธรรม เป็นหน่วยงานสนับสนุนการผลิตบัณฑิต โดยจัดกิจกรรมทำนุบำรุงศิลปวัฒนธรรมให้แก่นักเรียน นักศึกษา บุคลากรของมหาวิทยาลัยฯ และบุคคลทั่วไป อย่างต่อเนื่อง
                            </p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <!-- 2. แหล่งเรียนรู้ 3 บุรี -->
    <section id="learning-resources" class="container py-5">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <span class="section-tag">Learning Archives</span>
                <h2 class="section-title mb-0">แหล่งเรียนรู้ 3 บุรี</h2>
            </div>
            @if(isset($learningResources) && $learningResources->count() > 0)
                <a href="{{ route('contents.category', $learningResources->first()->category->slug) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 fw-semibold" style="color: var(--tru-purple); border-color: var(--tru-purple);">
                    ดูทั้งหมด <i class="bi bi-arrow-right ms-1"></i>
                </a>
            @endif
        </div>

        <!-- เพิ่มคลาส swipeable-row-mobile ตรงนี้ -->
        <div class="row g-4 swipeable-row-mobile">
            @forelse($learningResources ?? [] as $resource)
                <div class="col-md-6 col-lg-3">
                    <div class="news-card position-relative">
                        <span class="news-badge" style="background-color: #B45309;">{{ $resource->category->name ?? 'แหล่งเรียนรู้' }}</span>
                        <div class="ratio ratio-4x3 overflow-hidden bg-light">
                            <img src="{{ $resource->cover_image ? (Str::startsWith($resource->cover_image, ['http://', 'https://']) ? $resource->cover_image : asset('storage/'.$resource->cover_image)) : 'https://placehold.co/600x450' }}" class="w-100 h-100 object-fit-cover" alt="{{ $resource->title }}">
                        </div>
                        <div class="p-3">
                            <h6 class="fw-bold text-dark font-heading text-truncate mb-2">
                                <a href="{{ route('contents.show', $resource->slug) }}" class="text-dark text-decoration-none stretched-link">{{ $resource->title }}</a>
                            </h6>
                            <p class="text-muted small mb-0 text-truncate-2">{{ Str::limit(strip_tags($resource->body), 75) }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center text-muted py-4 border border-dashed rounded-4 bg-light">
                    ยังไม่มีข้อมูลแหล่งเรียนรู้ 3 บุรี ในระบบขณะนี้
                </div>
            @endforelse
        </div>
    </section>

    <!-- 3. โครงการ/กิจกรรม -->
    <section id="activities" class="bg-light py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <span class="section-tag">Projects & Activities</span>
                    <h2 class="section-title mb-0">โครงการ / กิจกรรม</h2>
                </div>
                @if(isset($activities) && $activities->count() > 0)
                    <a href="{{ route('contents.category', $activities->first()->category->slug) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 fw-semibold" style="color: var(--tru-purple); border-color: var(--tru-purple);">
                        ดูกิจกรรมทั้งหมด <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                @endif
            </div>

            <!-- เพิ่มคลาส swipeable-row-mobile ตรงนี้ -->
            <div class="row g-4 swipeable-row-mobile">
                @forelse($activities ?? [] as $act)
                    <div class="col-md-4">
                        <div class="news-card position-relative">
                            <span class="news-badge" style="background-color: #A21CAF;">{{ $act->category->name ?? 'กิจกรรม' }}</span>
                            <div class="ratio ratio-16x9 overflow-hidden bg-light">
                                <img src="{{ $act->cover_image ? (Str::startsWith($act->cover_image, ['http://', 'https://']) ? $act->cover_image : asset('storage/'.$act->cover_image)) : 'https://placehold.co/600x400' }}" class="w-100 h-100 object-fit-cover" alt="{{ $act->title }}">
                            </div>
                            <div class="p-4">
                                <small class="text-muted d-block mb-2"><i class="bi bi-calendar3 me-1"></i> {{ $act->published_at ? $act->published_at->format('d/m/Y') : $act->created_at->format('d/m/Y') }}</small>
                                <h6 class="fw-bold text-dark font-heading text-truncate mb-2">
                                    <a href="{{ route('contents.show', $act->slug) }}" class="text-dark text-decoration-none stretched-link">{{ $act->title }}</a>
                                </h6>
                                <p class="text-muted small mb-0 text-truncate-2">{{ Str::limit(strip_tags($act->body), 85) }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center text-muted py-4">ยังไม่มีรายการโครงการและกิจกรรม</div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- 4. แดชบอร์ด และงานพันธกิจสำคัญ -->
    <section id="missions" class="container">
        <div class="dashboard-section text-center px-4">
            <span class="section-tag">TRU Culture Services & Missions</span>
            <h2 class="section-title center mx-auto">แดชบอร์ด และงานพันธกิจสำคัญ</h2>
            <p class="text-muted mx-auto mb-5" style="max-width: 700px;">
                การบริหารจัดการและบริการสารสนเทศของสำนักศิลปะและวัฒนธรรม มหาวิทยาลัยราชภัฏเทพสตรี ครอบคลุมพันธกิจและบริการ 5 ด้าน
            </p>
            
            <!-- ปรับเป็น row-cols-2 สำหรับหน้าจอมือถือ (xs) ให้เป็น Grid แบบ 2 คอลัมน์ที่สวยงามแทนการเรียงยาว 1 คอลัมน์ -->
            <div class="row row-cols-2 row-cols-sm-2 row-cols-md-3 row-cols-lg-5 g-4 justify-content-center mt-2">
                <div class="col">
                    <div class="mission-card">
                        <div class="mission-icon"><i class="bi bi-bank2"></i></div>
                        <h5 class="fw-bold font-heading fs-6 mb-2 text-dark">แหล่งเรียนรู้</h5>
                        <p class="text-muted small mb-0 d-none d-md-block">คลังสารสนเทศประวัติศาสตร์ สถาปัตยกรรม พิพิธภัณฑ์ และแหล่งเรียนรู้ 3 บุรี</p>
                        <a href="{{ route('contents.category', 'three-buri-learning-resources') }}" class="stretched-link"></a>
                    </div>
                </div>

                <div class="col">
                    <div class="mission-card">
                        <div class="mission-icon"><i class="bi bi-journal-richtext"></i></div>
                        <h5 class="fw-bold font-heading fs-6 mb-2 text-dark">หนังสือและวารสาร</h5>
                        <p class="text-muted small mb-0 d-none d-md-block">รวบรวมวารสารวิชาการ เอกสารเผยแพร่ และสิ่งพิมพ์สตรีมมิ่งออนไลน์</p>
                        <a href="{{ route('contents.category', 'books-and-journals') }}" class="stretched-link"></a>
                    </div>
                </div>

                <div class="col">
                    <div class="mission-card">
                        <div class="mission-icon"><i class="bi bi-journal-bookmark-fill"></i></div>
                        <h5 class="fw-bold font-heading fs-6 mb-2 text-dark">งานวิจัยบทความ</h5>
                        <p class="text-muted small mb-0 d-none d-md-block">ผลงานศึกษาวิจัยเชิงลึก บทความวิชาการ และการยกระดับภูมิปัญญาท้องถิ่น</p>
                        <a href="{{ route('contents.category', 'research-and-articles') }}" class="stretched-link"></a>
                    </div>
                </div>

                <div class="col">
                    <div class="mission-card">
                        <div class="mission-icon"><i class="bi bi-calendar-event-fill"></i></div>
                        <h5 class="fw-bold font-heading fs-6 mb-2 text-dark">โครงการกิจกรรม</h5>
                        <p class="text-muted small mb-0 d-none d-md-block">โครงการทำนุบำรุงศิลปวัฒนธรรม และกิจกรรมส่งเสริมการเรียนรู้สู่สังคม</p>
                        <a href="{{ route('contents.category', 'projects-and-activities') }}" class="stretched-link"></a>
                    </div>
                </div>

                <div class="col">
                    <div class="mission-card">
                        <div class="mission-icon"><i class="bi bi-emoji-smile-fill"></i></div>
                        <h5 class="fw-bold font-heading fs-6 mb-2 text-dark">ประเมินความพึงพอใจ</h5>
                        <p class="text-muted small mb-0 d-none d-md-block">ระบบประเมินความพึงพอใจ และสรุปผลการให้บริการสารสนเทศแก่ผู้ใช้บริการ</p>
                        <a href="#" data-bs-toggle="modal" data-bs-target="#satisfactionSummaryModal" class="stretched-link"></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 5. หนังสือและวารสารสำนักฯ -->
    <section id="publications" class="container py-5">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <span class="section-tag">Publications</span>
                <h2 class="section-title mb-0">หนังสือและวารสารสำนักฯ</h2>
            </div>
            @if(isset($publications) && $publications->count() > 0)
                <a href="{{ route('contents.category', $publications->first()->category->slug) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 fw-semibold" style="color: var(--tru-purple); border-color: var(--tru-purple);">
                    ดูวารสารทั้งหมด <i class="bi bi-arrow-right ms-1"></i>
                </a>
            @endif
        </div>

        <!-- เพิ่มคลาส swipeable-row-mobile ตรงนี้ -->
        <div class="row g-4 swipeable-row-mobile">
            @forelse($publications ?? [] as $pub)
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="book-card-container">
                        <div class="book-card-3d">
                            @if($pub->cover_image)
                                <img src="{{ Str::startsWith($pub->cover_image, ['http://', 'https://']) ? $pub->cover_image : asset('storage/'.$pub->cover_image) }}" class="book-cover-img" alt="{{ $pub->title }}">
                            @else
                                <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-light text-muted p-3 text-center">
                                    <i class="bi bi-book fs-1 text-warning mb-2"></i>
                                    <span class="small font-heading fw-bold">สำนักศิลปะและวัฒนธรรม</span>
                                </div>
                            @endif

                            <div class="book-overlay-info">
                                <span class="badge mb-2" style="background-color: #15803D;">{{ $pub->category->name ?? 'สิ่งพิมพ์' }}</span>
                                <h6 class="fw-bold font-heading mb-2 text-white text-truncate-2" style="font-size: 0.92rem; line-height: 1.35;">{{ $pub->title }}</h6>
                                <a href="{{ route('contents.show', $pub->slug) }}" class="btn btn-xs btn-warning text-dark w-100 fw-bold rounded-pill shadow-sm stretched-link" style="font-size: 0.78rem;">
                                    <i class="bi bi-book-half me-1"></i> อ่านวารสาร
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center text-muted py-4">ไม่พบวารสารวิชาการออนแอร์ในระบบ</div>
            @endforelse
        </div>
    </section>

    <!-- 6. งานวิจัยและบทความ -->
    <section id="researches" class="bg-light py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <span class="section-tag">Research & Articles</span>
                    <h2 class="section-title mb-0">งานวิจัยและบทความ</h2>
                </div>
                @if(isset($researches) && $researches->count() > 0)
                    <a href="{{ route('contents.category', $researches->first()->category->slug) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 fw-semibold" style="color: var(--tru-purple); border-color: var(--tru-purple);">
                        ดูงานวิจัยทั้งหมด <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                @endif
            </div>

            <!-- เพิ่มคลาส swipeable-row-mobile ตรงนี้ -->
            <div class="row g-4 swipeable-row-mobile">
                @forelse($researches ?? [] as $research)
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="book-card-container">
                            <div class="book-card-3d">
                                @if($research->cover_image)
                                    <img src="{{ Str::startsWith($research->cover_image, ['http://', 'https://']) ? $research->cover_image : asset('storage/'.$research->cover_image) }}" class="book-cover-img" alt="{{ $research->title }}">
                                @else
                                    <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-white text-muted p-3 text-center" style="border-left: 6px solid {{ $research->category->color_code ?? '#1D4ED8' }};">
                                        <i class="bi bi-file-earmark-pdf fs-1 text-primary mb-2"></i>
                                        <span class="small font-heading fw-bold text-dark">{{ $research->category->name ?? 'วิจัย' }}</span>
                                    </div>
                                @endif

                                <div class="position-absolute top-0 start-0 w-100" style="height: 5px; background-color: {{ $research->category->color_code ?? '#1D4ED8' }}; z-index: 12;"></div>

                                <div class="book-overlay-info">
                                    <span class="badge mb-2" style="background-color: rgba(29, 78, 216, 0.9); color: #FFFFFF;">
                                        {{ $research->category->name ?? 'วิจัย' }}
                                    </span>
                                    <h6 class="fw-bold font-heading mb-1 text-white text-truncate-2" style="font-size: 0.92rem; line-height: 1.35;">{{ $research->title }}</h6>
                                    <p class="text-white-50 small mb-2 text-truncate-2" style="font-size: 0.75rem;">{{ Str::limit(strip_tags($research->body), 60) }}</p>
                                    
                                    <a href="{{ route('contents.show', $research->slug) }}" class="small fw-bold text-warning text-decoration-none stretched-link d-inline-flex align-items-center gap-1">
                                        อ่านงานวิจัย <i class="bi bi-chevron-right fs-7"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center text-muted py-4">ไม่พบข้อมูลงานวิจัยในระบบ</div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- 7. ข่าวประชาสัมพันธ์ -->
    <section id="news" class="container py-5">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <span class="section-tag">News & Announcements</span>
                <h2 class="section-title mb-0">ข่าวประชาสัมพันธ์</h2>
            </div>
            @if(isset($latestNews) && $latestNews->count() > 0)
                <a href="{{ route('contents.category', $latestNews->first()->category->slug) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 fw-semibold" style="color: var(--tru-purple); border-color: var(--tru-purple);">
                    ดูข่าวทั้งหมด <i class="bi bi-arrow-right ms-1"></i>
                </a>
            @endif
        </div>

        <!-- เพิ่มคลาส swipeable-row-mobile ตรงนี้ -->
        <div class="row g-4 swipeable-row-mobile">
            @forelse($latestNews ?? [] as $news)
                <div class="col-md-6 col-lg-4">
                    <div class="news-card position-relative">
                        <span class="news-badge">{{ $news->category->name ?? 'ข่าวสาร' }}</span>
                        <div class="ratio ratio-16x9 overflow-hidden bg-light">
                            <img src="{{ $news->cover_image ? (Str::startsWith($news->cover_image, ['http://', 'https://']) ? $news->cover_image : asset('storage/'.$news->cover_image)) : 'https://placehold.co/600x400' }}" class="w-100 h-100 object-fit-cover" alt="{{ $news->title }}">
                        </div>
                        <div class="p-4">
                            <small class="text-muted d-block mb-2"><i class="bi bi-calendar3 me-1"></i> {{ $news->published_at ? $news->published_at->format('d/m/Y') : $news->created_at->format('d/m/Y') }}</small>
                            <h5 class="fw-bold text-dark font-heading fs-6 mb-2 text-truncate">
                                <a href="{{ route('contents.show', $news->slug) }}" class="text-dark text-decoration-none stretched-link">{{ $news->title }}</a>
                            </h5>
                            <p class="text-muted small mb-0 text-truncate-2">{{ Str::limit(strip_tags($news->body), 100) }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center text-muted py-4">ยังไม่มีข่าวประชาสัมพันธ์ในระบบ</div>
            @endforelse
        </div>
    </section>

    <!-- 8 & 9. ปฏิทินกิจกรรม และ วิดีโอแนะนำ -->
    <section class="container py-5">
        <div class="row g-5">
            <!-- 8. ปฏิทินกิจกรรม / นัดหมาย -->
            <div class="col-lg-7">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <span class="section-tag">Calendar</span>
                        <h3 class="fw-bold text-dark mb-0">ปฏิทินกิจกรรม / นัดหมาย</h3>
                    </div>
                    <span class="badge text-white p-2 rounded-3" style="background-color: var(--tru-purple);">ประจำเดือนปัจจุบัน</span>
                </div>
                
                <div class="calendar-widget">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-calendar-check-fill text-warning me-2"></i> {{ $currentMonthName ?? 'ปฏิทิน' }}</h5>
                    </div>
                    
                    <div class="calendar-grid text-muted">
                        <div class="calendar-day-label">อา.</div>
                        <div class="calendar-day-label">จ.</div>
                        <div class="calendar-day-label">อ.</div>
                        <div class="calendar-day-label">พ.</div>
                        <div class="calendar-day-label">พฤ.</div>
                        <div class="calendar-day-label">ศ.</div>
                        <div class="calendar-day-label">ส.</div>

                        @foreach($calendarDays ?? [] as $day)
                            <div class="calendar-day 
                                {{ !$day['is_current_month'] ? 'text-black-50 opacity-50' : '' }} 
                                {{ $day['is_today'] ? 'active' : '' }} 
                                {{ $day['has_event'] && !$day['is_today'] ? 'fw-bold text-danger bg-danger bg-opacity-10' : '' }}">
                                {{ $day['day'] }}
                                @if($day['has_event'] && !$day['is_today'])
                                    <div style="width: 4px; height: 4px; background-color: #DC3545; border-radius: 50%; margin: 2px auto 0;"></div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="mt-4 p-3 bg-light rounded-3 border-start border-4" style="border-color: var(--tru-purple) !important;">
                        <div class="fw-semibold text-dark mb-2">กิจกรรมที่กำลังจะมาถึง</div>
                        @forelse($upcomingEvents ?? [] as $upEvent)
                            <div class="d-flex mb-2 pb-2 {{ !$loop->last ? 'border-bottom' : 'mb-0 pb-0' }}">
                                <div class="me-3 text-center" style="width: 45px;">
                                    <span class="d-block fw-bold text-danger lh-1">{{ $upEvent->event_date->format('d') }}</span>
                                    <small class="text-muted" style="font-size: 0.7rem;">{{ $upEvent->event_date->translatedFormat('M') }}</small>
                                </div>
                                <div>
                                    <p class="small mb-0 fw-bold text-dark">{{ $upEvent->title }}</p>
                                    <p class="small text-muted mb-0" style="font-size: 0.75rem;">
                                        <i class="bi bi-clock me-1"></i> 
                                        {{ $upEvent->start_time ? \Carbon\Carbon::parse($upEvent->start_time)->format('H:i') : 'ตลอดวัน' }}
                                        @if($upEvent->location) | <i class="bi bi-geo-alt ms-1 me-1"></i>{{ $upEvent->location }} @endif
                                    </p>
                                </div>
                            </div>
                        @empty
                            <p class="small mb-0 text-muted">ไม่มีกิจกรรมที่กำลังจะมาถึงในขณะนี้</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- 9. วิดีโอแนะนำและกิจกรรมเด่น -->
            <div class="col-lg-5">
                <span class="section-tag">Promotions & Video</span>
                <h3 class="fw-bold text-dark mb-4">วิดีโอแนะนำและกิจกรรมเด่น</h3>
                
                <div class="card border-0 rounded-4 shadow-sm overflow-hidden mb-4">
                    <div class="ratio ratio-16x9 bg-dark">
                        <iframe src="https://www.youtube.com/embed/{{ $featuredVideo->youtube_id ?? 'dQw4w9WgXcQ' }}?autoplay=1&mute=1&loop=1&playlist={{ $featuredVideo->youtube_id ?? 'dQw4w9WgXcQ' }}&controls=1" 
                                title="{{ $featuredVideo->title ?? 'วิดีโอแนะนำ' }}" 
                                frameborder="0" 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                allowfullscreen>
                        </iframe>
                    </div>
                    <div class="card-body bg-white">
                        <h6 class="fw-bold text-dark font-heading">{{ $featuredVideo->title ?? 'แนะนำสำนักศิลปะและวัฒนธรรม' }}</h6>
                        <p class="card-text text-muted small mb-0">{{ $featuredVideo->description ?? 'วีดิทัศน์ประชาสัมพันธ์การอนุรักษ์มรดกทางวัฒนธรรม' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ⚡ 10. ลิงก์ด่วนและระบบสารสนเทศ (พ่วง Modal ข้อเสนอแนะ/ร้องเรียน) -->
    <section id="portals" class="container">
        <div class="portal-bar">
            <div class="row align-items-center g-4">
                <div class="col-lg-4 text-center text-lg-start">
                    <h3 class="fw-bold mb-2 font-heading text-white">ลิงก์ด่วนและระบบสารสนเทศ</h3>
                    <p class="text-white-50 small mb-0">เข้าถึงข้อมูล บริหารจัดการ และส่งเรื่องร้องเรียนข้อเสนอแนะได้ในหนึ่งคลิก</p>
                </div>
                <div class="col-lg-8">
                    <div class="row g-3">
                        <div class="col-sm-7">
                            <!-- ⚡ ปุ่มกดเปิด Modal รับฟังความคิดเห็นและข้อเสนอแนะ ⚡ -->
                            <div class="portal-btn" data-bs-toggle="modal" data-bs-target="#feedbackModal">
                                <i class="bi bi-chat-left-text-fill fs-2 text-warning"></i>
                                <div class="text-start">
                                    <div class="fw-bold">ช่องทางรับฟังความคิดเห็น ข้อเสนอแนะ การร้องเรียน</div>
                                    <div class="small text-white-50" style="font-size: 0.72rem;">Complaints & Suggestions Gateway</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-5">
                            <a href="/page/%E0%B8%84%E0%B8%B9%E0%B9%88%E0%B8%A1%E0%B8%B7%E0%B8%AD%E0%B8%9B%E0%B8%A3%E0%B8%B0%E0%B8%8A%E0%B8%B2%E0%B8%8A%E0%B8%99-page-16-D7S2" class="portal-btn">
                                <i class="bi bi-person-workspace fs-2 text-warning"></i>
                                <div class="text-start">
                                    <div class="fw-bold">คู่มือประชาชน</div>
                                    <div class="small text-white-50" style="font-size: 0.72rem;">Citizen Service Manuals</div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 🌐 11. FACEBOOK PAGE EMBED (สำนักศิลปะและวัฒนธรรม) 🌐 -->
    <section id="facebook-feed" class="container py-4 mb-5">
        <div class="card border-0 rounded-4 shadow-sm overflow-hidden bg-white p-4" style="border-top: 4px solid var(--tru-purple-dark) !important;">
            <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3 text-primary d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="bi bi-facebook fs-3"></i>
                    </div>
                    <div>
                        <span class="section-tag mb-0">Social Media Feed</span>
                        <h3 class="fw-bold text-dark mb-0 font-heading fs-4">ติดตามข่าวสารผ่าน Facebook Page</h3>
                    </div>
                </div>
                <a href="https://www.facebook.com/artcultureTRU99/" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1">
                    <i class="bi bi-box-arrow-up-right"></i> เปิดบน Facebook
                </a>
            </div>

            <!-- Responsive Embed Wrapper -->
            <div class="d-flex justify-content-center">
                <div class="w-100 overflow-hidden" style="max-width: 500px; min-height: 500px;">
                    <iframe src="https://www.facebook.com/plugins/page.php?href=https%3A%2F%2Fwww.facebook.com%2FartcultureTRU99%2F&tabs=timeline&width=500&height=500&small_header=false&adapt_container_width=true&hide_cover=false&show_facepile=true&appId" 
                            width="100%" 
                            height="500" 
                            style="border:none; overflow:hidden; border-radius:12px;" 
                            scrolling="no" 
                            frameborder="0" 
                            allowfullscreen="true" 
                            allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share">
                    </iframe>
                </div>
            </div>
        </div>
    </section>

    <!-- ⚡ MODAL FORM: ช่องทางรับฟังความคิดเห็น ข้อเสนอแนะ และการร้องเรียน ⚡ -->
    <div class="modal fade" id="feedbackModal" tabindex="-1" aria-labelledby="feedbackModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                <div class="modal-header p-4 text-white" style="background: linear-gradient(135deg, var(--tru-purple-dark) 0%, #1E082A 100%); border-bottom: 3px solid var(--tru-gold);">
                    <div>
                        <h5 class="modal-title fw-bold font-heading text-white" id="feedbackModalLabel">
                            <i class="bi bi-chat-left-dots-fill text-warning me-2"></i>ช่องทางรับฟังความคิดเห็น ข้อเสนอแนะ และการร้องเรียน
                        </h5>
                        <p class="small text-white-50 mb-0">สำนักศิลปะและวัฒนธรรม มหาวิทยาลัยราชภัฏเทพสตรี</p>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('feedback.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4 bg-light">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">ประเภทเรื่องที่ต้องการส่ง <span class="text-danger">*</span></label>
                                <select name="type" class="form-select border-0 shadow-sm p-2.5" required>
                                    <option value="SUGGESTION">ข้อเสนอแนะ (Suggestion)</option>
                                    <option value="COMPLAINT">เรื่องร้องเรียน (Complaint)</option>
                                    <option value="FEEDBACK">ความคิดเห็นทั่วไป (Feedback)</option>
                                    <option value="GENERAL">สอบถามข้อมูล (General Inquiry)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">หัวข้อเรื่อง <span class="text-danger">*</span></label>
                                <input type="text" name="subject" class="form-control border-0 shadow-sm p-2.5" placeholder="ระบุหัวข้อเรื่อง..." required>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-dark small">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                                <input type="text" name="fullname" class="form-control border-0 shadow-sm p-2.5" placeholder="ชื่อ-นามสกุลของคุณ" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-dark small">อีเมล (ถ้ามี)</label>
                                <input type="email" name="email" class="form-control border-0 shadow-sm p-2.5" placeholder="example@email.com">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-dark small">เบอร์โทรศัพท์ (ถ้ามี)</label>
                                <input type="tel" name="phone" class="form-control border-0 shadow-sm p-2.5" placeholder="08X-XXX-XXXX">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small">รายละเอียดข้อความ <span class="text-danger">*</span></label>
                            <textarea name="message" class="form-control border-0 shadow-sm p-3" rows="4" placeholder="ระบุรายละเอียด ข้อเสนอแนะ หรือเรื่องร้องเรียนของคุณ..." required></textarea>
                        </div>

                        <div class="p-3 bg-white rounded-3 border small text-muted">
                            <i class="bi bi-shield-lock-fill text-success me-1"></i> ข้อมูลของคุณจะถูกเก็บรักษาเป็นความลับอย่างเคร่งครัดและนำไปปรับปรุงการให้บริการของสำนักฯ
                        </div>
                    </div>
                    <div class="modal-footer p-3 bg-white border-top">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-warning fw-bold text-dark rounded-pill px-4" style="background-color: var(--tru-gold); border: none;">
                            <i class="bi bi-send-fill me-1"></i> ส่งข้อความ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ⚡ MODAL: สรุปผลความพึงพอใจต่อบริการ (Satisfaction Summary Dashboard) ⚡ -->
    <div class="modal fade" id="satisfactionSummaryModal" tabindex="-1" aria-labelledby="satisfactionSummaryLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                <!-- Modal Header: Gradient -->
                <div class="modal-header p-4 border-0" style="background: linear-gradient(135deg, var(--tru-purple-dark, #4C1D95) 0%, #1E082A 100%); position: relative;">
                    <div class="position-relative z-index-1">
                        <h5 class="modal-title fw-bold font-heading text-white mb-1" id="satisfactionSummaryLabel">
                            <i class="bi bi-bar-chart-line-fill text-warning me-2"></i>สรุปผลความพึงพอใจต่อบริการ
                        </h5>
                        <p class="small text-white-50 mb-0">
                            {{-- ดึงข้อมูลช่วงเวลา ถ้าไม่มีให้แสดง Default --}}
                            ข้อมูลประเมินผล {{ $satisfactionData->period ?? 'อยู่ระหว่างการรวบรวมข้อมูล' }}
                        </p>
                    </div>
                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3 shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                    
                    <!-- Background Pattern แบบจางๆ -->
                    <i class="bi bi-emoji-smile-fill position-absolute text-white opacity-10" style="font-size: 8rem; right: -20px; top: -30px;"></i>
                </div>

                <div class="modal-body p-0 bg-light">
                    
                    {{-- Logic คำนวณดาวและข้อความประเมินผล (ป้องกัน Error กรณีไม่มีข้อมูล) --}}
                    @php
                        $rating = $satisfactionData->overall_rating ?? 0;
                        $fullStars = floor($rating); // ดาวเต็ม
                        $halfStar = ($rating - $fullStars) >= 0.5 ? 1 : 0; // ดาวครึ่งดวง
                        $emptyStars = 5 - $fullStars - $halfStar; // ดาวว่างเปล่า

                        // คำนวณระดับข้อความ
                        if ($rating >= 4.5) $levelText = 'ดีเยี่ยม';
                        elseif ($rating >= 3.5) $levelText = 'ดีมาก';
                        elseif ($rating >= 2.5) $levelText = 'ดี';
                        elseif ($rating >= 1.5) $levelText = 'พอใช้';
                        elseif ($rating > 0) $levelText = 'ต้องปรับปรุง';
                        else $levelText = 'ยังไม่มีคะแนน';
                    @endphp

                    <!-- ส่วนที่ 1: คะแนนรวม (Overall Rating) -->
                    <div class="bg-white p-4 text-center border-bottom">
                        <h1 class="display-3 fw-bold mb-0" style="color: var(--tru-purple-dark, #4C1D95);">
                            {{ number_format($rating, 1) }} <span class="fs-4 text-muted fw-normal">/ 5</span>
                        </h1>
                        
                        <!-- Render ดวงดาวอัตโนมัติ ตามคะแนนจริง -->
                        <div class="text-warning fs-4 mb-2">
                            @for($i = 0; $i < $fullStars; $i++)
                                <i class="bi bi-star-fill"></i>
                            @endfor
                            
                            @if($halfStar)
                                <i class="bi bi-star-half"></i>
                            @endif
                            
                            @for($i = 0; $i < $emptyStars; $i++)
                                <i class="bi bi-star"></i>
                            @endfor
                        </div>
                        
                        <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fw-semibold">
                            ระดับความพึงพอใจ: {{ $levelText }}
                        </span>
                        <p class="text-muted small mt-3 mb-0">
                            จากผู้เข้าร่วมประเมินทั้งหมด 
                            <strong class="text-dark">{{ number_format($satisfactionData->total_respondents ?? 0) }}</strong> ท่าน
                        </p>
                    </div>

                    <!-- ส่วนที่ 2: แยกตามมิติ (Dimensions Progress Bars) -->
                    <div class="p-4">
                        <h6 class="fw-bold text-dark font-heading mb-4 text-center">สัดส่วนความพึงพอใจรายด้าน</h6>
                        
                        <!-- ด้านการให้บริการ -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-end mb-1">
                                <span class="small fw-semibold text-dark"><i class="bi bi-check2-circle text-primary me-1"></i> ด้านกระบวนการ/ขั้นตอนการให้บริการ</span>
                                <span class="small fw-bold text-primary">{{ $satisfactionData->dimension_service ?? 0 }}%</span>
                            </div>
                            <div class="progress" style="height: 10px; border-radius: 10px; background-color: #E2E8F0;">
                                <div class="progress-bar bg-primary" role="progressbar" 
                                    style="width: {{ $satisfactionData->dimension_service ?? 0 }}%" 
                                    aria-valuenow="{{ $satisfactionData->dimension_service ?? 0 }}" 
                                    aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>

                        <!-- ด้านบุคลากร -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-end mb-1">
                                <span class="small fw-semibold text-dark"><i class="bi bi-person-badge text-success me-1"></i> ด้านเจ้าหน้าที่/บุคลากรผู้ให้บริการ</span>
                                <span class="small fw-bold text-success">{{ $satisfactionData->dimension_staff ?? 0 }}%</span>
                            </div>
                            <div class="progress" style="height: 10px; border-radius: 10px; background-color: #E2E8F0;">
                                <div class="progress-bar bg-success" role="progressbar" 
                                    style="width: {{ $satisfactionData->dimension_staff ?? 0 }}%" 
                                    aria-valuenow="{{ $satisfactionData->dimension_staff ?? 0 }}" 
                                    aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>

                        <!-- ด้านสถานที่ -->
                        <div class="mb-2">
                            <div class="d-flex justify-content-between align-items-end mb-1">
                                <span class="small fw-semibold text-dark"><i class="bi bi-building text-warning me-1"></i> ด้านสถานที่และสิ่งอำนวยความสะดวก</span>
                                <span class="small fw-bold text-warning" style="color: #D4AF37 !important;">{{ $satisfactionData->dimension_facility ?? 0 }}%</span>
                            </div>
                            <div class="progress" style="height: 10px; border-radius: 10px; background-color: #E2E8F0;">
                                <div class="progress-bar" role="progressbar" 
                                    style="width: {{ $satisfactionData->dimension_facility ?? 0 }}%; background-color: #D4AF37;" 
                                    aria-valuenow="{{ $satisfactionData->dimension_facility ?? 0 }}" 
                                    aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer p-3 bg-white border-top justify-content-center">
                    <p class="small text-muted mb-0">
                        <i class="bi bi-info-circle me-1"></i> สำนักศิลปะและวัฒนธรรม มุ่งมั่นพัฒนาบริการอย่างต่อเนื่อง
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL POPUP GATEWAY -->
    @if(isset($activePopup))
        <div class="modal fade" id="marketingPopupGate" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 bg-transparent">
                    <div class="modal-body p-0 position-relative">
                        <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3 shadow-none" data-bs-dismiss="modal"></button>
                        <a href="{{ $activePopup->url ?? '#' }}" class="d-block">
                            <img src="{{ asset('storage/' . $activePopup->image_path) }}" class="img-fluid rounded-4 shadow w-100" alt="{{ $activePopup->title }}">
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var el = document.getElementById('marketingPopupGate');
                    if(el) {
                        var modalInst = new bootstrap.Modal(el);
                        modalInst.show();
                    } 
                });
            </script>
        @endpush
    @endif

@endsection