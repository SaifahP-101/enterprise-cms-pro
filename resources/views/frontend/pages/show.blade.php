@extends('layouts.frontend')

<!-- ========================================================================= -->
<!-- 1. SEO & PAGE META INJECTION                                             -->
<!-- ========================================================================= -->
@section('seo')
    <title>{{ $page->meta_title ?? $page->title }} | สำนักศิลปะและวัฒนธรรม มรภ.เทพสตรี</title>
    <meta name="description" content="{{ Str::limit(strip_tags($page->meta_description ?? $page->body), 160) }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $page->meta_title ?? $page->title }}">
    <meta property="og:description" content="{{ Str::limit(strip_tags($page->meta_description ?? $page->body), 150) }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        /* 🔒 บล็อกการสั่งพิมพ์ผ่านหน้าต่าง Print ของเบราว์เซอร์ */
        @media print {
            body, html, #securePageVault, #pdfViewerContainer {
                display: none !important;
                visibility: hidden !important;
            }
        }
        .secure-canvas-area {
            user-select: none !important;
            -webkit-user-select: none !important;
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
<!-- 2. MAIN PAGE CONTENT WORKSPACE                                            -->
<!-- ========================================================================= -->
@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        
        <div class="col-lg-10 secure-view-active" id="securePageVault">
            <article class="card border-0 shadow-sm p-4 p-md-5 rounded-4 bg-white mb-4">
                
                <!-- ดัชนีสถิติเข้าชม -->
                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-3">
                    <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-3 py-2 rounded-pill font-heading">
                        <i class="bi bi-file-earmark-richtext-fill me-1"></i> หน้าเพจสารสนเทศ
                    </span>
                    <div class="text-muted small fw-medium font-body">
                        <i class="bi bi-eye-fill text-success me-1"></i> เข้าชมสะสม: 
                        <strong class="text-dark">{{ number_format($page->views_count ?? $page->view_count ?? 0) }}</strong> ครั้ง
                    </div>
                </div>

                <!-- 📌 หัวข้อหลักพาดหัวหน้าเพจ -> 18pt -->
                <h1 class="fw-bold text-dark mb-4 font-heading cultural-heading" style="line-height: 1.4; font-size: 18pt !important;">
                    {{ $page->title }}
                </h1>

                <!-- ⚡ CKEditor 5 Body Workspace (Google Fonts Sarabun Spec) -->
                <div class="content-body-text ck-content lh-lg text-secondary mb-5">
                    {!! $page->body !!}
                </div>

                <!-- 🔒 SECURE VAULT CONTAINER & PDF.JS CANVAS STREAMING -->
                @if($page->secure_pdf_path)
                    <div class="p-4 bg-light rounded-4 border border-danger border-opacity-25 mb-4 shadow-3xs secure-canvas-area">
                        <div class="mb-3">
                            <h2 class="text-danger fw-bold mb-1 font-heading d-flex align-items-center gap-2" style="font-size: 16pt !important;">
                                <i class="bi bi-shield-lock-fill"></i> เอกสารแนบสิทธิ์ควบคุมความปลอดภัยสูงสุด
                            </h2>
                            <p class="small text-muted mb-0">ระบบแสดงผลผ่านพิกเซลพารามิเตอร์ เพื่อป้องกันการสั่งพิมพ์ คัดลอก หรือบันทึกไฟล์ดิบภายนอก</p>
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

            </article>     
        </div>

    </div>
</div>
@endsection

<!-- ========================================================================= -->
<!-- 3. FRONTEND SECURITY SHIELDS & PDF.JS SCRIPTS                            -->
<!-- ========================================================================= -->
@push('scripts')
<script src="{{ asset('vendor/pdfjs/pdf.min.js') }}"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        
        // 🔒 1. FRONTEND SECURITY GUARD: บล็อกคลิกขวา และสั่งพิมพ์ Ctrl+P / Ctrl+S
        var secureTarget = document.getElementById('securePageVault');
        if (secureTarget) {
            secureTarget.addEventListener('contextmenu', function (e) { 
                e.preventDefault(); 
                return false; 
            });
        }

        window.addEventListener('keydown', function (e) {
            var isKeyP = (e.key === 'p' || e.keyCode === 80);
            var isKeyS = (e.key === 's' || e.keyCode === 83);
            var isMeta = (e.ctrlKey || e.metaKey);

            if (isMeta && (isKeyP || isKeyS)) {
                e.preventDefault();
                e.stopPropagation();
                alert('🚨 ระบบรักษาความปลอดภัย:\nไม่อนุญาตให้สั่งพิมพ์เอกสาร หรือบันทึกไฟล์หน้าเพจนี้');
                return false;
            }
        });

        // 🛡️ 2. PDF.JS CANVAS STREAMING ENGINE
        @if($page->secure_pdf_path)
            if (typeof pdfjsLib !== 'undefined') {
                pdfjsLib.GlobalWorkerOptions.workerSrc = '{{ asset("vendor/pdfjs/pdf.worker.min.js") }}';
                
                // สตรีมมิ่งไฟล์ผ่าน Secure Stream Controller Path
                const pdfStreamUrl = "{{ route('secure.pdf.stream', ['filename' => basename($page->secure_pdf_path)]) }}";
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
                            
                            // บล็อกคลิกขวาบนตัวแคนวาสเรนเดอร์ PDF
                            canvas.addEventListener('contextmenu', function(e) { 
                                e.preventDefault(); 
                                return false; 
                            });
                            
                            workspace.appendChild(canvas);
                            page.render({ canvasContext: context, viewport: viewport });
                        });
                    }
                }).catch(function(error) {
                    if (loader) {
                        loader.innerHTML = '<div class="text-danger fw-bold"><i class="bi bi-exclamation-triangle-fill me-1"></i> เซสชันการเชื่อมต่อไฟล์หมดอายุหรือไม่พบไฟล์ในคลังความปลอดภัย</div>';
                    }
                });
            }
        @endif

    });
</script>
@endpush