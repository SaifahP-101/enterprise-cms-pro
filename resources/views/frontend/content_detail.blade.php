@extends('layouts.app')

@section('seo')
    <!--  ระบบ Inject ดันคะแนน SEO & Open Graph อัตโนมัติในระดับชั้นแกนเลย์เอาต์ -->
    <title>{{ $content->meta_title ?? $content->title }} | สำนักศิลปะและวัฒนธรรม มรภ.เทพสตรี</title>
    <meta name="description" content="{{ $content->meta_description ?? Str::limit(strip_tags($content->body), 140) }}">
    
    <!-- แท็กยิงประมวลผลการแชร์ลง Line/Facebook (Open Graph Configuration) -->
    <meta property="og:type" content="article">
    <meta property="og:title" content="{{ $content->meta_title ?? $content->title }}">
    <meta property="og:description" content="{{ $content->meta_description ?? Str::limit(strip_tags($content->body), 140) }}">
    <meta property="og:url" content="{{ url()->current() }}">
    @if($content->cover_image)
        <meta property="og:image" content="{{ asset('storage/' . $content->cover_image) }}">
    @endif
@endsection

@section('content')
<div class="row">
    <div class="col-lg-9 mx-auto bg-white p-4 p-md-5 shadow-sm rounded border-top border-4" style="border-color: #DAA520 !important;">
        
        <!-- ข้อมูลเส้นทางเมนูด้านบน -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-decoration-none text-secondary">หน้าแรก</a></li>
                <li class="breadcrumb-item text-secondary active">{{ $content->category->name }}</li>
            </ol>
        </nav>

        <!-- พาดหัวหลักและวิดเจ็ตสถิติป้องกันรีเฟรช (Anti-F5 view counter) -->
        <h1 class="fw-bold text-dark mt-3 mb-2 h2 lh-sm">{{ $content->title }}</h1>
        <div class="d-flex flex-wrap gap-3 align-items-center text-muted small pb-3 mb-4 border-bottom">
            <span> เผยแพร่เมื่อ: <strong>{{ $content->published_at->format('d/m/Y H:i') }} น.</strong></span>
            <span>️ ยอดเข้าชม: <strong>{{ number_format($content->view_count) }} ครั้ง</strong></span>
        </div>

        <!--  แสดงผลภาพหน้าปกข่าวสารหลัก -->
        @if($content->cover_image)
            <div class="mb-4 text-center rounded overflow-hidden shadow-xs">
                <img src="{{ asset('storage/' . $content->cover_image) }}" class="img-fluid w-100" style="max-height: 480px; object-fit: cover;" alt="Image">
            </div>
        @endif

        <!--  กระดานเนื้อหาความเรียบร้อยทางภาษา -->
        <div class="content-body text-dark lh-lg fs-5 mb-5" style="text-align: justify;">
            {!! $content->body !!}
        </div>

        <!--  NEW: การเรนเดอร์ฝังจอภาพวิดีโอ YouTube อัตโนมัติ (หากแอดมินใส่ลิงก์มา) -->
        @if($content->youtube_url)
            @php
                // ตรรกะแยกแยะรหัสวิดีโอจาก URL รูปแบบต่างๆ ของ YouTube ในระดับเบรด
                $videoId = '';
                if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $content->youtube_url, $match)) {
                    $videoId = $match[1];
                }
            @endphp
            @if($videoId)
                <div class="card p-3 mb-5 border-0 shadow-xs bg-light rounded">
                    <h6 class="fw-bold text-dark mb-2"> วิดีโอสื่อประกอบการเรียนรู้และกิจกรรม</h6>
                    <div class="ratio ratio-16x9 rounded overflow-hidden shadow-xs">
                        <iframe src="https://www.youtube.com/embed/{{ $videoId }}" title="YouTube video player" allowfullscreen></iframe>
                    </div>
                </div>
            @endif
        @endif

        <!--  NEW: ช่องสตรีมเอกสารลับ PDF 30MB คุ้มครองความปลอดภัยสากล -->
        @if($content->secure_pdf_path)
            <div class="alert p-4 border-start border-4 border-warning bg-light d-flex align-items-center justify-content-between flex-wrap gap-3 mb-5 shadow-xs">
                <div>
                    <h5 class="h6 fw-bold text-dark mb-1"><i class="bi bi-folder"></i> เอกสารประกอบวิชาการ/ประกาศสงวนสิทธิ์ (Secure PDF Document)</h5>
                    <p class="text-muted small mb-0">ระบบเปิดอ่านสตรีมมิ่งไบนารี ปิดกั้นการเข้าถึงที่อยู่ลิงก์ตรงภายนอกเซิร์ฟเวอร์</p>
                </div>
                <!-- วิ่งผ่านท่อความปลอดภัย Secure File Gateway ยืนยันสิทธิ์ -->
                <a href="{{ route('secure.pdf.stream', ['filename' => $content->secure_pdf_path]) }}" 
                   class="btn btn-dark btn-sm px-4 py-2 text-warning fw-bold shadow-sm" target="_blank">
                     เปิดอ่านเอกสารคุ้มครองสิทธิ์
                </a>
            </div>
        @endif

        <!--  NEW: ระบบลูปแท็กคีย์เวิร์ดท้ายข่าวสาร (Tags System UI Component) -->
        @if($content->tags->count() > 0)
            <div class="pt-3 border-top mb-2">
                <span class="text-secondary small fw-bold me-2"> แท็กสืบค้นข้อมูล:</span>
                @foreach($content->tags as $tag)
                    <a href="{{ url('/tag/' . $tag->slug) }}" class="badge text-decoration-none text-dark bg-light border p-2 me-1 mb-1 hover-gold shadow-2xs">
                        #{{ $tag->name }}
                    </a>
                @endforeach
            </div>
        @endif
        
    </div>
</div>
@endsection