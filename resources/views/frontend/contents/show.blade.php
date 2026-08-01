@extends('layouts.frontend')

<!-- ========================================================================= -->
<!-- 1. ADVANCED SEO & OPEN GRAPH TAGS INJECTION COMPONENT -->
<!-- ========================================================================= -->
@section('seo')
    <title>{{ $content->meta_title ?? $content->title }} | สำนักศิลปะและวัฒนธรรม มรภ.เทพสตรี</title>
    <meta name="description" content="{{ Str::limit(strip_tags($content->meta_description ?? $content->body), 160) }}">
    <meta name="keywords" content="@foreach($content->tags as $t){{ $t->name }},@endforeachภูมิปัญญาลพบุรี,มรภ.เทพสตรี">
    <meta name="author" content="{{ $content->user->name ?? 'สำนักศิลปะและวัฒนธรรม' }}">
    <link rel="canonical" href="{{ route('contents.show', $content->slug) }}">
    
    <!-- Facebook & LINE Open Graph Specifications -->
    <meta property="og:type" content="article">
    <meta property="og:title" content="{{ $content->meta_title ?? $content->title }}">
    <meta property="og:description" content="{{ Str::limit(strip_tags($content->meta_description ?? $content->body), 150) }}">
    @if($content->cover_image)
        <meta property="og:image" content="{{ Str::startsWith($content->cover_image, ['http://', 'https://']) ? $content->cover_image : asset('storage/' . $content->cover_image) }}">
    @endif
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="สำนักศิลปะและวัฒนธรรม มรภ.เทพสตรี">

    <!-- Twitter Card Framework -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $content->title }}">
    <meta name="twitter:description" content="{{ Str::limit(strip_tags($content->body), 150) }}">
    @if($content->cover_image)
        <meta name="twitter:image" content="{{ Str::startsWith($content->cover_image, ['http://', 'https://']) ? $content->cover_image : asset('storage/' . $content->cover_image) }}">
    @endif

    <!-- CSS Shield สำหรับความปลอดภัยชั้นหน้าบ้าน และสไตล์ Magnific Popup -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/magnific-popup.min.css">
    <style>
        @media print {
            body, html, #secureContentVault, #pdfViewerContainer {
                display: none !important;
                visibility: hidden !important;
            }
        }
        .secure-canvas-area {
            user-select: none !important;
            -webkit-user-select: none !important;
            -ms-user-select: none !important;
        }
        .custom-gallery-hover img {
            transition: transform 0.3s ease;
        }
        .custom-gallery-hover:hover img {
            transform: scale(1.06);
        }
    </style>
@endsection

<!-- ========================================================================= -->
<!-- 2. MAIN CONTENT ARTICLE WORKSPACE -->
<!-- ========================================================================= -->
@section('content')
<div class="container my-5">
    <div class="row g-4">
        
        <!-- ฝั่งซ้าย: ข้อมูลบทความหลัก การเล่นวิดีโอ และการล็อกไฟล์ PDF ป้องกันระบบเชิงลึก -->
        <div class="col-lg-8 secure-view-active" id="secureContentVault">
            <article class="card border-0 shadow-sm p-4 p-md-5 rounded-4 bg-white mb-4">
                
                <!-- ส่วนหัวและดัชนีสถิติแบบ Anti-N+1 Optimized -->
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 border-bottom pb-3">
                    <span class="badge bg-warning text-dark fw-bold px-3 py-2 rounded-pill">
                        <i class="bi bi-folder-fill me-1"></i> {{ $content->category->name }}
                    </span>
                    <div class="d-flex align-items-center gap-3 text-muted small fw-medium">
                        <span>
                            <i class="bi bi-eye-fill text-success me-1"></i> ยอดเข้าชม: 
                            <strong class="text-dark">{{ number_format($content->view_count) }}</strong> ครั้ง
                        </span>
                        <span class="vr opacity-25"></span>
                        <span>
                            <i class="bi bi-share-fill text-danger me-1"></i> แชร์: 
                            <strong id="meta-share-counter" class="text-dark">{{ number_format($content->share_count) }}</strong> ครั้ง
                        </span>
                    </div>
                </div>

                <!-- ชื่อหัวข้อและรายละเอียดเวลาสร้างสรรค์ชิ้นงาน -->
                <h1 class="h2 fw-bold text-dark mb-3 font-heading" style="line-height: 1.4;">{{ $content->title }}</h1>
                <p class="text-muted small mb-4">
                    <i class="bi bi-calendar3 text-primary me-1"></i> วันที่เผยแพร่: 
                    {{ $content->published_at ? $content->published_at->format('d/m/Y') : $content->created_at->format('d/m/Y') }}
                </p>

                <!-- 🖼️ รูปปกหลักแบบ Full Img -->
                @if($content->cover_image)
                    <div class="mb-4 text-center zoom-gallery">
                        <a href="{{ Str::startsWith($content->cover_image, ['http://', 'https://']) ? $content->cover_image : asset('storage/' . $content->cover_image) }}" class="d-block rounded-4 overflow-hidden border bg-light shadow-2xs">
                            <img src="{{ Str::startsWith($content->cover_image, ['http://', 'https://']) ? $content->cover_image : asset('storage/' . $content->cover_image) }}" class="img-fluid w-100 h-auto d-block" style="object-fit: contain; max-height: none;" alt="{{ $content->title }}">
                        </a>
                    </div>
                @endif

                <!-- พื้นที่เรนเดอร์ตัวเนื้อหาบทความหลัก -->
                <div class="content-body-text lh-lg text-secondary mb-5" style="text-align: justify; font-size: 1.05rem;">
                    {!! $content->body !!}
                </div>

                <!-- 🎥 🎬 YOUTUBE EMBEDDED PLAYER COMPONENT -->
                @if(!empty($content->youtube_url))
                    @php
                        // Regex สำหรับแกะเอาเฉพาะ Video ID จาก URL YouTube ทุกรูปแบบ
                        $youtubeId = null;
                        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $content->youtube_url, $matches)) {
                            $youtubeId = $matches[1];
                        }
                    @endphp

                    @if($youtubeId)
                        <div class="mb-5">
                            <h5 class="fw-bold text-dark mb-3 font-heading d-flex align-items-center gap-2">
                                <i class="bi bi-youtube text-danger fs-4"></i> วิดีโอรับชมประกอบบทความ
                            </h5>
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

                <!-- 🔒 SECURE VAULT CONTAINER: จุดกางม่านเอกสารลับ PDF ผ่าน HTML5 Canvas -->
                @if($content->secure_pdf_path)
                    <div class="p-4 bg-light rounded-4 border border-danger border-opacity-25 mb-4 shadow-3xs secure-canvas-area">
                        <h5 class="text-danger fw-bold mb-1 font-heading d-flex align-items-center gap-2">
                            <i class="bi bi-shield-lock-fill"></i> เอกสารสิทธิ์คุ้มครองความปลอดภัยขั้นสูง
                        </h5>
                        <p class="small text-muted mb-3">ระบบแปลงไฟล์ข้อมูลผ่านกลไกพิกเซลเพื่อป้องกันการเซฟไฟล์ ห้ามคัดลอก ถ่ายภาพ หรือสั่งพิมพ์เด็ดขาด</p>
                        
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
                            <h6 class="fw-bold text-dark mb-1 font-heading"><i class="bi bi-share-fill text-danger me-2"></i>ส่งต่อภูมิปัญญาสู่สังคม</h6>
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

        <!-- ฝั่งขวา: รายชื่อแท็ก และ คลังภาพแกลเลอรี -->
        <div class="col-lg-4">
            
            <div class="card border-0 shadow-sm p-4 rounded-4 bg-white mb-4">
                <h5 class="fw-bold text-dark border-bottom pb-2 mb-3 font-heading">
                    <i class="bi bi-tags-fill text-secondary me-2"></i>คีย์เวิร์ดแท็กสืบค้น
                </h5>
                <div class="d-flex flex-wrap gap-2">
                    @forelse($content->tags as $tag)
                        <span class="badge bg-light text-secondary border px-3 py-2 rounded-3 fw-medium" style="font-size: 0.85rem;">
                            <i class="bi bi-hash text-muted"></i>{{ $tag->name }}
                        </span>
                    @empty
                        <span class="small text-muted italic p-2"><i class="bi bi-info-circle me-1"></i>ไม่มีการผูกแท็กคำค้นไว้สำหรับบทความนี้</span>
                    @endforelse
                </div>
            </div>

            <div class="card border-0 shadow-sm p-4 rounded-4 bg-white">
                <h5 class="fw-bold text-dark border-bottom pb-2 mb-3 font-heading">
                    <i class="bi bi-images text-primary me-2"></i>คลังภาพแกลเลอรีกิจกรรมกลุ่ม
                </h5>
                <div class="row row-cols-2 g-2 zoom-gallery">
                    @forelse($content->galleries as $gallery)
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
@endsection

<!-- ========================================================================= -->
<!-- 🛡️ 3. ADVANCED FRONTEND SECURITY GUARD & VISUAL ENGINE -->
<!-- ========================================================================= -->
@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/jquery.magnific-popup.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        
        // 🔒 บล็อกคำสั่งคลิกขวา
        var secureTarget = document.getElementById('secureContentVault');
        if (secureTarget) {
            secureTarget.addEventListener('contextmenu', function (e) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            });
        }

        // 🔒 บล็อกปุ่มคีย์บอร์ดสั่งพิมพ์ (Ctrl+P) และบันทึก (Ctrl+S)
        window.addEventListener('keydown', function (e) {
            var isKeyP = (e.key === 'p' || e.keyCode === 80);
            var isKeyS = (e.key === 's' || e.keyCode === 83);
            var isMetaActive = (e.ctrlKey || e.metaKey); 

            if (isMetaActive && (isKeyP || isKeyS)) {
                e.preventDefault();
                e.stopPropagation();
                alert('🚨 ระบบรักษาความปลอดภัยข้อมูลสารสนเทศองค์กร:\nไม่อนุญาตให้จัดพิมพ์เอกสาร หรือบันทึกไฟล์เด็ดขาด');
                return false;
            }
        });

        // 🖼️ MAGNIFIC POPUP ENGINE
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
                            return 'สถาบันศิลปะและวัฒนธรรม มรภ.เทพสตรี';
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

        // 🛡️ HTML5 CANVAS PDF STREAMING VAULT ENGINE
        @if($content->secure_pdf_path)
            if (typeof pdfjsLib !== 'undefined') {
                pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';
                
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

                            const renderContext = { canvasContext: context, viewport: viewport };
                            page.render(renderContext);
                        });
                    }
                }).catch(function(error) {
                    console.error('PDF Streaming Error:', error);
                    if(loader) loader.innerHTML = '<div class="text-danger fw-bold"><i class="bi bi-exclamation-triangle-fill"></i> เซสชันการเชื่อมต่อไฟล์หมดอายุ หรือไฟล์สูญหาย</div>';
                });
            }
        @endif

        // 📢 AJAX SOCIAL SHARE
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
    });
</script>
@endpush