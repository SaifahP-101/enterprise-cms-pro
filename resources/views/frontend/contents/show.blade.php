@extends('layouts.frontend')

<!-- ========================================================================= -->
<!-- 1. SEO & PAGE META INJECTION                                             -->
<!-- ========================================================================= -->
@section('seo')
    <title>{{ $content->meta_title ?? $content->title }} | สำนักศิลปะและวัฒนธรรม มรภ.เทพสตรี</title>
    <meta name="description" content="{{ Str::limit(strip_tags($content->meta_description ?? $content->body), 160) }}">
    <meta property="og:type" content="article">
    <meta property="og:title" content="{{ $content->title }}">
    <meta property="og:description" content="{{ Str::limit(strip_tags($content->body), 150) }}">
    @if($content->cover_image)
        <meta property="og:image" content="{{ asset('storage/' . $content->cover_image) }}">
    @endif
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('vendor/magnific-popup/magnific-popup.min.css') }}">
    
    <style>
        /* 🔒 บล็อกการสั่งพิมพ์ผ่านหน้าต่าง Print ของเบราว์เซอร์ */
        @media print {
            body, html, #secureContentVault, #pdfViewerContainer {
                display: none !important;
                visibility: hidden !important;
            }
        }
        .secure-canvas-area {
            user-select: none !important;
            -webkit-user-select: none !important;
        }
        .btn-tru-gold-download {
            background: linear-gradient(135deg, var(--tru-gold, #D4AF37) 0%, #B45309 100%);
            color: #FFFFFF !important;
            font-weight: 600;
            border-radius: 30px;
            padding: 10px 24px;
            border: none;
            box-shadow: 0 4px 12px rgba(212, 175, 55, 0.35);
            transition: all 0.25s ease;
        }
        .btn-tru-gold-download:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(212, 175, 55, 0.5);
            color: #FFFFFF !important;
        }

        .custom-gallery-hover img {
            transition: transform 0.3s ease;
        }
        .custom-gallery-hover:hover img {
            transform: scale(1.06);
        }

        /* ========================================================================= */
        /* 🔤 STRICT TYPOGRAPHY SPECIFICATION (18pt / 16pt / 14pt)                  */
        /* ========================================================================= */
        .content-body-text,
        .content-body-text *,
        .ck-content,
        .ck-content * {
            font-family: 'Sarabun', sans-serif !important;
        }

        /* 📌 1. เนื้อหาหลักแบบตั้งต้น (Base Body) -> 14pt */
        .content-body-text,
        .ck-content {
            font-size: 14pt !important;
            line-height: 1.75 !important;
            color: #334155 !important;
            word-break: break-word;
        }

        /* 📌 2. หัวข้อหลัก (Main Heading - h1) -> 18pt Bold */
        .content-body-text :is(h1, h1 *),
        .ck-content :is(h1, h1 *) {
            font-size: 18pt !important;
            font-weight: 700 !important;
            color: #3F1551 !important;
            line-height: 1.4 !important;
            margin-top: 1.5rem !important;
            margin-bottom: 0.75rem !important;
        }

        /* 📌 3. หัวข้อรอง (Sub Headings - h2, h3, h4, h5, h6) -> 16pt Bold */
        .content-body-text :is(h2, h2 *, h3, h3 *, h4, h4 *, h5, h5 *, h6, h6 *),
        .ck-content :is(h2, h2 *, h3, h3 *, h4, h4 *, h5, h5 *, h6, h6 *) {
            font-size: 16pt !important;
            font-weight: 700 !important;
            color: #4C1D95 !important;
            line-height: 1.4 !important;
            margin-top: 1.25rem !important;
            margin-bottom: 0.5rem !important;
        }

        /* 📌 4. ย่อหน้า รายการ ข้อความทั่วไป ตาราง และลิงก์ -> 14pt Regular */
        .content-body-text :is(p, p *, li, li *, span, span *, div, div *, td, th, a),
        .ck-content :is(p, p *, li, li *, span, span *, div, div *, td, th, a) {
            /* font-size: 14pt !important; */
            line-height: 1.75 !important;
        }

        /* 🖼️ รูปภาพประกอบเนื้อหา */
        .content-body-text figure.image,
        .ck-content figure.image {
            margin: 1.5rem auto !important;
            text-align: center;
            max-width: 100%;
        }
        .content-body-text :is(img, figure.image img),
        .ck-content :is(img, figure.image img) {
            max-width: 100% !important;
            height: auto !important;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        .content-body-text figure.image figcaption,
        .ck-content figure.image figcaption {
            font-size: 12pt !important;
            color: #64748B;
            margin-top: 8px;
            font-style: italic;
        }

        /* 📊 ตารางภายในเนื้อหา */
        .content-body-text table,
        .ck-content table {
            width: 100% !important;
            border-collapse: collapse !important;
            margin: 1.5rem 0 !important;
        }
        .content-body-text :is(th, td),
        .ck-content :is(th, td) {
            border: 1px solid #E2E8F0 !important;
            padding: 10px 14px !important;
            font-size: 14pt !important;
        }
        .content-body-text th,
        .ck-content th {
            background-color: #F3E8FF !important;
            color: #3F1551 !important;
            font-weight: 700 !important;
        }

        /* 💬 บล็อกอ้างอิง */
        .content-body-text blockquote,
        .ck-content blockquote {
            border-left: 4px solid #D4AF37 !important;
            background-color: #FEFDFC;
            padding: 14px 22px !important;
            margin: 1.5rem 0 !important;
            color: #475569;
            font-style: italic;
            border-radius: 0 10px 10px 0;
        }

        .flex-grow-1 { 
            background-color: #f8f9fa;
        }
    </style>
@endsection

<!-- ========================================================================= -->
<!-- 2. MAIN CONTENT ARTICLE WORKSPACE                                        -->
<!-- ========================================================================= -->
@section('content')
<div class="container my-5">
    <div class="row g-4">
        
        <!-- 👈 ฝั่งซ้าย: ข้อมูลบทความหลัก และ PDF Secure Canvas -->
        <div class="col-lg-8 secure-view-active" id="secureContentVault">
            <article class="card border-0 shadow-sm p-4 p-md-5 rounded-4 bg-white mb-4">
                
                <!-- ดัชนีสถิติ: เข้าชม / ดาวน์โหลด / แชร์ -->
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 border-bottom pb-3">
                    <span class="badge bg-warning text-dark fw-bold px-3 py-2 rounded-pill">
                        <i class="bi bi-folder-fill me-1"></i> {{ $content->category->name ?? 'หมวดหมู่ทั่วไป' }}
                    </span>
                    <div class="d-flex align-items-center gap-3 text-muted small fw-medium">
                        <span>
                            <i class="bi bi-eye-fill text-success me-1"></i> เข้าชม: 
                            <strong class="text-dark">{{ number_format($content->view_count) }}</strong> ครั้ง
                        </span>
                        <span class="vr opacity-25"></span>
                        <span>
                            <i class="bi bi-download text-primary me-1"></i> ดาวน์โหลด: 
                            <strong id="meta-download-counter" class="text-dark">{{ number_format($content->download_count ?? 0) }}</strong> ครั้ง
                        </span>
                        <span class="vr opacity-25"></span>
                        <span>
                            <i class="bi bi-share-fill text-danger me-1"></i> แชร์: 
                            <strong id="meta-share-counter" class="text-dark">{{ number_format($content->share_count) }}</strong> ครั้ง
                        </span>
                    </div>
                </div>

                <!-- 📌 หัวข้อหลักพาดหัวบทความ -> 18pt -->
                <h1 class="fw-bold text-dark mb-3 font-heading" style="line-height: 1.4; font-size: 18pt !important;">{{ $content->title }}</h1>
                <p class="text-muted small mb-4">
                    <i class="bi bi-calendar3 text-primary me-1"></i> วันที่เผยแพร่: 
                    {{ $content->published_at ? $content->published_at->format('d/m/Y') : $content->created_at->format('d/m/Y') }}
                    @if($content->user)
                        <span class="ms-2"><i class="bi bi-person-fill text-secondary me-1"></i> โดย: {{ $content->user->name }}</span>
                    @endif
                </p>

                @if($content->cover_image)
                    <div class="mb-4 text-center">
                        <img src="{{ Str::startsWith($content->cover_image, ['http://', 'https://']) ? $content->cover_image : asset('storage/' . $content->cover_image) }}" class="img-fluid rounded-4 shadow-sm" alt="{{ $content->title }}">
                    </div>
                @endif

                <!-- ⚡ CKEditor 5 Content Workspace -->
                <div class="content-body-text ck-content lh-lg text-secondary mb-5">
                    {!! $content->body !!}
                </div>

                <!-- 🎥 YOUTUBE EMBEDDED PLAYER COMPONENT -->
                @if(!empty($content->youtube_url))
                    @php
                        $youtubeId = null;
                        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $content->youtube_url, $matches)) {
                            $youtubeId = $matches[1];
                        }
                    @endphp

                    @if($youtubeId)
                        <div class="mb-5">
                            <h2 class="fw-bold text-dark mb-3 font-heading d-flex align-items-center gap-2" style="font-size: 16pt !important;">
                                <i class="bi bi-youtube text-danger fs-4"></i> วิดีโอรับชมประกอบบทความ
                            </h2>
                            <div class="ratio ratio-16x9 rounded-4 overflow-hidden shadow-sm border bg-dark">
                                <iframe src="https://www.youtube.com/embed/{{ $youtubeId }}?rel=0" 
                                        title="{{ $content->title }}" 
                                        frameborder="0" 
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                                        allowfullscreen>
                                </iframe>
                            </div>
                        </div>
                    @endif
                @endif

                <!-- 🔒 SECURE VAULT CONTAINER & PDF DOWNLOAD ACTION -->
                @if($content->secure_pdf_path)
                    <div class="p-4 bg-light rounded-4 border border-danger border-opacity-25 mb-4 shadow-3xs secure-canvas-area">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
                            <div>
                                <h2 class="text-danger fw-bold mb-1 font-heading d-flex align-items-center gap-2" style="font-size: 16pt !important;">
                                    <i class="bi bi-shield-lock-fill"></i> เอกสารสิทธิ์คุ้มครองความปลอดภัยขั้นสูง
                                </h2>
                                <p class="small text-muted mb-0">ระบบแสดงผลผ่านพิกเซลเพื่อป้องกันการบันทึกไฟล์ดิบโดยตรง</p>
                            </div>

                            <!-- 📥 ปุ่มเปิด Modal ขอรับการดาวน์โหลด PDF -->
                            <div>
                                <button type="button" class="btn btn-tru-gold-download text-nowrap d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#downloadRequesterModal">
                                    <i class="bi bi-file-earmark-pdf-fill fs-5"></i>
                                    <span>ดาวน์โหลดฉบับเต็ม</span>
                                    <span class="badge bg-white text-dark rounded-pill ms-1" id="btn-download-counter">{{ number_format($content->download_count ?? 0) }}</span>
                                </button>
                            </div>
                        </div>
                        
                        <!-- PDF.js Canvas Workspace -->
                        <div id="pdfViewerContainer" class="position-relative rounded-3 border bg-secondary shadow-2xs overflow-auto p-3 d-flex flex-column align-items-center gap-3" style="max-height: 650px; background-color: #525659 !important;">
                            <div id="pdfRenderLoading" class="text-center text-white py-5 my-4">
                                <div class="spinner-border text-warning mb-3" role="status"></div>
                                <div class="font-heading fw-bold">กำลังสตรีมมิ่งโครงสร้างไฟล์ความปลอดภัยสูง...</div>
                            </div>
                            <div id="pdfCanvasWorkspace" class="w-100 d-flex flex-column align-items-center gap-3"></div>
                        </div>
                    </div>
                @endif

                <!-- กล่องแผงควบคุมการแชร์ข้อมูลส่งต่อโซเชียล -->
                <div class="card border-0 bg-light rounded-4 p-4 mt-4 shadow-3xs">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div>
                            <h3 class="fw-bold text-dark mb-1 font-heading" style="font-size: 16pt !important;"><i class="bi bi-share-fill text-danger me-2"></i>ส่งต่อภูมิปัญญาสู่สังคม</h3>
                            <small class="text-muted">ร่วมเป็นส่วนหนึ่งในการเผยแพร่องค์ความรู้ทางศิลปวัฒนธรรมท้องถิ่นลพบุรี</small>
                        </div>
                        
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-white border text-dark px-3 py-2.5 me-2 shadow-3xs rounded-pill" style="font-size: 0.85rem;">
                                ยอดส่งต่อ: <strong id="share-count-display" class="text-danger fs-6 mx-1">{{ number_format($content->share_count) }}</strong> ครั้ง
                            </span>

                            <button class="btn btn-primary rounded-circle btn-share-action shadow-3xs d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; background-color: #1877F2; border: none;" data-platform="facebook" data-id="{{ $content->id }}" data-url="{{ urlencode(url()->current()) }}">
                                <i class="bi bi-facebook fs-5"></i>
                            </button>

                            <button class="btn btn-success rounded-circle btn-share-action shadow-3xs d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; background-color: #06C755; border: none;" data-platform="line" data-id="{{ $content->id }}" data-url="{{ urlencode(url()->current()) }}">
                                <i class="bi bi-line fs-5"></i>
                            </button>

                            <button class="btn btn-dark rounded-circle btn-share-action shadow-3xs d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;" data-platform="copy" data-id="{{ $content->id }}" data-url="{{ urlencode(url()->current()) }}">
                                <i class="bi bi-link-45deg fs-4"></i>
                            </button>
                        </div>
                    </div>
                </div>   
            </article>     
        </div>

        <!-- 👉 ฝั่งขวา: รายชื่อแท็ก และ แกลเลอรี -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm p-4 rounded-4 bg-white mb-4">
                <h2 class="fw-bold text-dark border-bottom pb-2 mb-3 font-heading" style="font-size: 16pt !important;"><i class="bi bi-tags-fill text-secondary me-2"></i>คีย์เวิร์ดแท็ก</h2>
                <div class="d-flex flex-wrap gap-2">
                    @forelse($content->tags ?? [] as $tag)
                        <span class="badge bg-light text-secondary border px-3 py-2 rounded-3 fw-medium"><i class="bi bi-hash text-muted"></i>{{ $tag->name }}</span>
                    @empty
                        <span class="small text-muted italic">ไม่มีแท็ก</span>
                    @endforelse
                </div>
            </div>

            <div class="card border-0 shadow-sm p-4 rounded-4 bg-white">
                <h2 class="fw-bold text-dark border-bottom pb-2 mb-3 font-heading" style="font-size: 16pt !important;">
                    <i class="bi bi-images text-primary me-2"></i>คลังภาพแกลเลอรีกิจกรรมกลุ่ม
                </h2>
                <div class="row row-cols-2 g-2 zoom-gallery">
                    @forelse($content->galleries ?? [] as $gallery)
                        <div class="col">
                            <a href="{{ Str::startsWith($gallery->file_path, ['http://', 'https://']) ? $gallery->file_path : asset('storage/' . $gallery->file_path) }}" class="d-block rounded-3 border overflow-hidden shadow-3xs position-relative custom-gallery-hover" style="height: 110px;">
                                <img src="{{ Str::startsWith($gallery->file_path, ['http://', 'https://']) ? $gallery->file_path : asset('storage/' . $gallery->file_path) }}" class="w-100 h-100" style="object-fit: cover;" alt="ภาพกิจกรรมสถาบัน">
                            </a>
                        </div>
                    @empty
                        <div class="col-12 text-muted small py-4 text-center">
                            <i class="bi bi-image-alt fs-2 opacity-25 d-block mb-1"></i>คลังสารสนเทศนี้ยังไม่มีรูปภาพแกลเลอรีเพิ่มเติม
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</div>

<!-- ========================================================================= -->
<!-- 📥 MODAL FORM: แบบฟอร์มกรอกข้อมูลผู้ขอรับเอกสารก่อนดาวน์โหลด PDF             -->
<!-- ========================================================================= -->
@if($content->secure_pdf_path)
<div class="modal fade" id="downloadRequesterModal" tabindex="-1" aria-labelledby="downloadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            
            <div class="modal-header border-0 pb-0" style="background: linear-gradient(135deg, #3F1551 0%, #1E082A 100%); color: #FFF; border-radius: 1rem 1rem 0 0;">
                <div class="p-2">
                    <h5 class="modal-title font-heading fw-bold text-warning" id="downloadModalLabel" style="font-size: 16pt !important;">
                        <i class="bi bi-file-earmark-arrow-down-fill me-2"></i>แบบฟอร์มขอรับเอกสารสารสนเทศ
                    </h5>
                    <p class="small text-white-50 mb-2">โปรดกรอกข้อมูลเพื่อบันทึกสถิติการให้บริการตามระเบียบองค์กร</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="pdfDownloadForm" action="{{ route('contents.download', $content->id) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    
                    <div id="modalAlertError" class="alert alert-danger d-none rounded-3 small"></div>

                    <div class="mb-3">
                        <label for="req_fullname" class="form-label font-heading fw-semibold small text-dark">
                            ชื่อ-นามสกุล ผู้ขอรับเอกสาร <span class="text-danger">*</span>
                        </label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light"><i class="bi bi-person-fill text-muted"></i></span>
                            <input type="text" class="form-control" id="req_fullname" name="fullname" placeholder="ระบุชื่อและนามสกุลจริง" required>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label for="req_email" class="form-label font-heading fw-semibold small text-dark">อีเมลติดต่อ</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light"><i class="bi bi-envelope-fill text-muted"></i></span>
                                <input type="email" class="form-control" id="req_email" name="email" placeholder="example@domain.com">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="req_phone" class="form-label font-heading fw-semibold small text-dark">เบอร์โทรศัพท์</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light"><i class="bi bi-telephone-fill text-muted"></i></span>
                                <input type="tel" class="form-control" id="req_phone" name="phone" placeholder="08X-XXX-XXXX">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="req_organization" class="form-label font-heading fw-semibold small text-dark">หน่วยงาน / สถาบัน / คณะ</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light"><i class="bi bi-building text-muted"></i></span>
                            <input type="text" class="form-control" id="req_organization" name="organization" placeholder="ระบุหน่วยงานหรือสถานศึกษา">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="req_purpose" class="form-label font-heading fw-semibold small text-dark">วัตถุประสงค์การนำไปใช้ประโยชน์</label>
                        <textarea class="form-control form-control-sm" id="req_purpose" name="purpose" rows="2" placeholder="เช่น เพื่อการศึกษา, งานวิจัย, อ้างอิงทางวิชาการ ฯลฯ"></textarea>
                    </div>

                </div>

                <div class="modal-footer bg-light border-0 rounded-bottom-4 px-4 py-3">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" id="btnSubmitDownload" class="btn btn-sm btn-tru-gold-download rounded-pill px-4">
                        <i class="bi bi-download me-1"></i> ยืนยันและดาวน์โหลด
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/magnific-popup/jquery.magnific-popup.min.js') }}"></script>
<script src="{{ asset('vendor/pdfjs/pdf.min.js') }}"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        
        // 🔒 1. FRONTEND SECURITY GUARD: บล็อกคลิกขวา และสั่งพิมพ์ Ctrl+P / Ctrl+S
        var secureTarget = document.getElementById('secureContentVault');
        if (secureTarget) {
            secureTarget.addEventListener('contextmenu', function (e) { e.preventDefault(); return false; });
        }
        window.addEventListener('keydown', function (e) {
            if ((e.ctrlKey || e.metaKey) && (e.key === 'p' || e.keyCode === 80 || e.key === 's' || e.keyCode === 83)) {
                e.preventDefault();
                alert('🚨 ระบบรักษาความปลอดภัย: ไม่อนุญาตให้จัดพิมพ์หรือเซฟไฟล์เด็ดขาด');
                return false;
            }
        });

        // 🛡️ 2. PDF.JS CANVAS STREAMING ENGINE
        @if($content->secure_pdf_path)
            if (typeof pdfjsLib !== 'undefined') {
                pdfjsLib.GlobalWorkerOptions.workerSrc = '{{ asset("vendor/pdfjs/pdf.worker.min.js") }}';
                const pdfStreamUrl = "{{ route('secure.pdf.stream', ['filename' => basename($content->secure_pdf_path)]) }}";
                const workspace = document.getElementById('pdfCanvasWorkspace');
                const loader = document.getElementById('pdfRenderLoading');

                pdfjsLib.getDocument(pdfStreamUrl).promise.then(function(pdf) {
                    if (loader) loader.style.display = 'none';
                    for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                        pdf.getPage(pageNum).then(function(page) {
                            const viewport = page.getViewport({ scale: 1.5 });
                            const canvas = document.createElement('canvas');
                            const context = canvas.getContext('2d');
                            canvas.height = viewport.height;
                            canvas.width = viewport.width;
                            canvas.className = 'w-100 shadow rounded-3 bg-white mb-2';
                            canvas.addEventListener('contextmenu', function(e) { e.preventDefault(); return false; });
                            workspace.appendChild(canvas);
                            page.render({ canvasContext: context, viewport: viewport });
                        });
                    }
                }).catch(function(error) {
                    if (loader) loader.innerHTML = '<div class="text-danger fw-bold"><i class="bi bi-exclamation-triangle-fill"></i> เซสชันการเชื่อมต่อไฟล์หมดอายุ</div>';
                });
            }
        @endif

        // 📥 3. AJAX REQUEST HANDLING: แบบฟอร์มขอรับเอกสารดาวน์โหลด
        const pdfDownloadForm = document.getElementById('pdfDownloadForm');
        if (pdfDownloadForm) {
            pdfDownloadForm.addEventListener('submit', function (e) {
                e.preventDefault();

                const submitBtn = document.getElementById('btnSubmitDownload');
                const alertError = document.getElementById('modalAlertError');
                const formData = new FormData(this);
                const modalElement = document.getElementById('downloadRequesterModal');
                const bootstrapModal = bootstrap.Modal.getInstance(modalElement);

                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>กำลังประมวลผล...';
                alertError.classList.add('d-none');

                fetch(this.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="bi bi-download me-1"></i> ยืนยันและดาวน์โหลด';

                    if (data.success) {
                        // อัปเดตตัวเลขอัตโนมัติแบบ Real-time
                        const formattedCount = new Intl.NumberFormat().format(data.new_count);
                        const metaCounter = document.getElementById('meta-download-counter');
                        const btnCounter = document.getElementById('btn-download-counter');
                        if (metaCounter) metaCounter.innerText = formattedCount;
                        if (btnCounter) btnCounter.innerText = formattedCount;

                        if (bootstrapModal) bootstrapModal.hide();
                        pdfDownloadForm.reset();

                        if (data.download_url) {
                            window.location.href = data.download_url;
                        }
                    } else {
                        let errorHtml = '<strong>ไม่สามารถดำเนินการได้:</strong><br>';
                        if (data.errors && Array.isArray(data.errors)) {
                            errorHtml += data.errors.join('<br>');
                        } else {
                            errorHtml += (data.message || 'เกิดข้อผิดพลาดในการบันทึกข้อมูล');
                        }
                        alertError.innerHTML = errorHtml;
                        alertError.classList.remove('d-none');
                    }
                })
                .catch(error => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="bi bi-download me-1"></i> ยืนยันและดาวน์โหลด';
                    alertError.innerHTML = 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์ กรุณาลองใหม่อีกครั้ง';
                    alertError.classList.remove('d-none');
                });
            });
        }

        // 📢 4. AJAX SOCIAL SHARE METRICS
        const shareButtons = document.querySelectorAll('.btn-share-action');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        shareButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const platform = this.getAttribute('data-platform');
                const contentId = this.getAttribute('data-id');
                const rawUrl = this.getAttribute('data-url'); 
                const unencodedUrl = decodeURIComponent(rawUrl);

                if (csrfToken) {
                    fetch('/content/' + contentId + '/share', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const countDisplay = document.getElementById('share-count-display');
                            const metaDisplay = document.getElementById('meta-share-counter');
                            const formattedNum = new Intl.NumberFormat().format(data.new_count);
                            if (countDisplay) countDisplay.innerText = formattedNum;
                            if (metaDisplay) metaDisplay.innerText = formattedNum;
                        }
                    })
                    .catch(error => console.error('Share Metrics Sync Interrupted:', error));
                }

                const windowConfig = `width=600,height=520,left=${(window.screen.width/2)-300},top=${(window.screen.height/2)-260},resizable=yes,scrollbars=yes`;
                if (platform === 'facebook') window.open('https://www.facebook.com/sharer/sharer.php?u=' + rawUrl, 'Share_Facebook', windowConfig);
                else if (platform === 'line') window.open('https://social-plugins.line.me/lineit/share?url=' + rawUrl, 'Share_LINE', windowConfig);
                else if (platform === 'copy') {
                    navigator.clipboard.writeText(unencodedUrl).then(function() { 
                        alert('📋 คัดลอกลิงก์เรียบร้อยแล้ว สามารถนำไปส่งต่อได้ทันที'); 
                    });
                }
            });
        });

        // 🖼️ 5. MAGNIFIC POPUP ENGINE
        if (typeof $.fn.magnificPopup !== 'undefined') {
            $('.zoom-gallery').each(function() {
                $(this).magnificPopup({
                    delegate: 'a',
                    type: 'image',
                    closeOnContentClick: false,
                    closeBtnInside: false,
                    mainClass: 'mfp-with-zoom mfp-img-mobile',
                    image: {
                        verticalFit: true,
                        titleSrc: function() {
                            return 'สำนักศิลปะและวัฒนธรรม มรภ.เทพสตรี';
                        }
                    },
                    gallery: {
                        enabled: true,
                        navigateByImgClick: true,
                        preload: [0,1]
                    },
                    zoom: {
                        enabled: true,
                        duration: 300, 
                        opener: function(element) {
                            return element.find('img');
                        }
                    }
                });
            });
        }
    });
</script>
@endpush