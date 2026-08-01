@extends('layouts.frontend')

<!--  Dynamic SEO & Open Graph Ready For Static Page -->
@section('seo')
    <title>{{ $page->meta_title ?? $page->title }} | สำนักศิลปะและวัฒนธรรม มรภ.เทพสตรี</title>
    <meta name="description" content="{{ Str::limit(strip_tags($page->meta_description ?? $page->body), 160) }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $page->meta_title ?? $page->title }}">
    <meta property="og:description" content="{{ Str::limit(strip_tags($page->meta_description ?? $page->body), 150) }}">
    <meta property="og:url" content="{{ url()->current() }}">
@endsection

@section('content')
<div class="container animate__animated animate__fadeIn">
    <div class="row justify-content-center">
        <div class="col-lg-10 secure-view-active" id="securePageVault">
            
            <div class="card border-0 shadow-sm p-4 p-md-5 rounded-3 bg-white">
                <!-- หัวเรื่องหลักหน้าเพจ -->
                <h1 class="h1 fw-bold text-dark mb-3 border-bottom pb-3 cultural-heading">{{ $page->title }}</h1>
                
                <!-- ยอดสถิติเข้าชมสะสมเชิงสถิติ -->
                <div class="text-end mb-4">
                    <span class="text-muted small"><i class="bi bi-speedometer2"></i> สถิติการเปิดอ่านหน้านี้: <strong>{{ number_format($page->view_count) }}</strong> ครั้ง</span>
                </div>

                <!-- พื้นที่เรนเดอร์เนื้อหาหลักจาก CKEditor 5 -->
                <div class="content-detail-area lh-lg mb-5" style="text-align: justify; font-size: 1.05rem;">
                    {!! $page->body !!}
                </div>

                <!--  SECURE DOCUMENT VAULT INTEGRATION -->
                @if($page->secure_pdf_path)
                    <div class="p-4 rounded-3 border border-danger bg-light mt-4">
                        <h5 class="text-danger fw-bold mb-2 d-flex align-items-center gap-2">
                            <i class="bi bi-shield-check"></i> เอกสารแนบสิทธิ์ควบคุมความปลอดภัยสูงสุด
                        </h5>
                        <p class="small text-muted mb-3">ห้ามคัดลอก ปริ๊นท์ หรือดาวน์โหลดภายนอกระบบสตรีมมิ่งเด็ดขาด</p>
                        
                        <div class="ratio ratio-16x9 rounded border overflow-hidden shadow-sm" style="background-color: #525659;">
                            <iframe src="{{ route('secure.pdf.stream', ['filename' => $page->secure_pdf_path]) }}#toolbar=0&navpanes=0&scrollbar=0"></iframe>
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection

<!--  FRONTEND SECURITY SHIELDS SCRIPTS -->
@section('security_scripts')
<script>
    (function () {
        var vault = document.getElementById('securePageVault');
        if (vault) {
            vault.addEventListener('contextmenu', function (e) { e.preventDefault(); return false; });
        }

        window.addEventListener('keydown', function (e) {
            var isKeyP = (e.key === 'p' || e.keyCode === 80);
            var isKeyS = (e.key === 's' || e.keyCode === 83);
            var isMeta = (e.ctrlKey || e.metaKey);

            if (isMeta && (isKeyP || isKeyS)) {
                e.preventDefault();
                e.stopPropagation();
                alert(' ระบบรักษาความปลอดภัย:\nไม่อนุญาตให้สั่งพิมพ์เอกสาร หรือบันทึกไฟล์หน้าเพจนี้');
                return false;
            }
        });
    })();
</script>
@endsection