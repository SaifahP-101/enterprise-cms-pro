@extends('layouts.admin')

@section('title', 'สร้างหน้าเพจอิสระใหม่')

@section('admin_content')
<div class="container-fluid animate__animated animate__fadeIn">
    <div class="mb-4">
        <a href="{{ route('admin.pages.index') }}" class="text-decoration-none small text-secondary">← กลับสู่คลังหน้าเพจอิสระ</a>
        <h2 class="h4 mb-1 text-dark fw-bold mt-2"><i class="bi bi-laptop"></i> จัดสร้างหน้าเพจโครงสร้างอิสระชิ้นใหม่</h2>
        <p class="text-muted small">ระบบจะทำการแปลงชื่อเรื่องเป็น URL คีย์เส้นทางด่วน (Slug) อัตโนมัติในระดับ Model Lifecycle</p>
    </div>

    <!--  ฟอร์มหลัก: รองรับการแนบเอกสารลับด้วย multipart/form-data -->
    <form action="{{ route('admin.pages.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row g-4">
            
            <!--  ฝั่งซ้าย: ข้อมูลเนื้อหาหลัก, บก.ข้อความ และแผงควบคุม SEO -->
            <div class="col-lg-8">
                <div class="card main-content-card p-4 shadow-sm border-0 mb-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">หัวข้อหน้าเพจหลัก (Page Title) *</label>
                        <input type="text" name="title" id="pageTitleInput" class="form-control shadow-2xs" placeholder="เช่น ประวัติความเป็นมาสำนักฯ, นโยบายความเป็นส่วนตัว..." required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">กำหนดคีย์เส้นทางด่วนเอง (Custom Slug) ถ้ามี</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light small text-muted">{{ url('/page') }}/</span>
                            <input type="text" name="slug" id="pageSlugInput" class="form-control shadow-2xs" placeholder="history-and-background">
                        </div>
                        <div class="form-text text-muted small">หากปล่อยว่างไว้ ระบบจะคำนวณเอาชื่อหัวข้อด้านบนมาแปลงเป็น Slug ภาษาไทย/อังกฤษให้อัตโนมัติ</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">รายละเอียดเนื้อหาภายในเพจ (CKEditor 5 Classic Build) *</label>
                        <textarea id="ckPageEditor" name="body"></textarea>
                    </div>
                </div>

                <!--  แผงควบคุมดันอันดับการค้นหา (SEO Optimization Panel) -->
                <div class="card main-content-card p-4 shadow-sm border-0" style="border-top: 4px solid #DAA520;">
                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3"> ส่วนบริหารจัดการคะแนนอันดับการค้นหา (SEO Panel)</h5>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">หัวข้อเจาะจงการแสดงผลบน Google (Meta Title)</label>
                        <input type="text" name="meta_title" class="form-control form-control-sm shadow-2xs" placeholder="หากปล่อยว่างระบบจะอิงตามหัวข้อหน้าเพจหลักด้านบนอัตโนมัติ">
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold text-secondary">คำโปรยเนื้อหาจำลองสั้นเมื่อแชร์ลงโซเชียล (Meta Description)</label>
                        <textarea name="meta_description" class="form-control form-control-sm shadow-2xs" rows="3" placeholder="สรุปใจความสำคัญของหน้าเพจอิสระชิ้นนี้ เพื่อเพิ่มประสิทธิภาพการคลิกของบอทค้นหา..."></textarea>
                    </div>
                </div>
            </div>

            <!--  ฝั่งขวา: สถานะการเผยแพร่, เอกสาร PDF ปิดลับ และปุ่มคำสั่ง -->
            <div class="col-lg-4">
                <div class="card main-content-card p-4 shadow-sm border-0 position-sticky" style="top: 20px;">
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="bi bi-gear"></i> การเผยแพร่และระบบป้องกันสิทธิ์</h6>
                    
                    <!--  Secure PDF Attachment -->
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-danger"><i class="bi bi-folder"></i> แนบไฟล์เอกสารสำคัญ PDF (ไม่เกิน 30 MB)</label>
                        <input type="file" name="secure_pdf" class="form-control form-control-sm shadow-3xs" accept="application/pdf">
                        <small class="text-muted d-block mt-2 lh-base">
                            <i class="bi bi-shield-check"></i> ไฟล์นี้จะถูกจัดเก็บเข้าสู่ <strong>Secure Vault Folder</strong> สตรีมมิ่งผ่านรหัสไบนารีหน้าบ้าน และล็อกการคลิกขวา/ห้ามสั่งปริ๊นท์อัตโนมัติ
                        </small>
                    </div>

                    <!-- สถานะสวิตช์เปิด-ปิดหน้าเพจ -->
                    <div class="form-check form-switch mb-4 p-3 bg-light rounded border ms-0">
                        <input type="checkbox" name="is_active" class="form-check-input ms-0 me-2" id="pageActiveSwitch" checked>
                        <label class="form-check-label small fw-bold text-success" for="pageActiveSwitch">เปิดให้ออนไลน์หน้าร้านทันที</label>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.pages.index') }}" class="btn btn-sm btn-secondary w-50 py-2 fw-medium">← ย้อนกลับ</a>
                        <button type="submit" class="btn btn-sm btn-success w-50 py-2 shadow-sm fw-bold"><i class="bi bi-floppy"></i> บันทึกติดตั้งเพจ</button>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>
@endsection

@push('admin_scripts')
<script>
    $(document).ready(function() {
        // 1. บูตระบบชุดเครื่องมือจัดทำคำข้อความ CKEditor 5
        ClassicEditor.create(document.querySelector('#ckPageEditor'), {
            toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'insertTable', 'blockQuote', 'undo', 'redo']
        }).then(editor => { 
            editor.ui.view.editable.element.style.minHeight = '380px'; 
            editor.ui.view.editable.element.style.backgroundColor = '#FFFFFF';
        }).catch(error => console.error(error));

        // 2. ลอจิกแปลงชื่อข้อความเป็นพาร์ทคีย์ทางผ่านภาษาอังกฤษเบื้องต้น (Slug Auto-cleaner)
        $('#pageTitleInput').on('input', function() {
            var rawText = $(this).val();
            // เคลียร์อักขระพิเศษแปลกปลอมออกสำหรับเคสที่พิมพ์ภาษาอังกฤษปน
            var cleanedSlug = rawText.toLowerCase()
                                     .replace(/[^a-z0-8ก-๙\s-]/g, '')
                                     .replace(/\s+/g, '-');
            // หากแอดมินไม่ได้ระบุ Slug เอง ให้ลอกตามชื่อหัวข้อ
            if($('#pageSlugInput').val() == '') {
                // คงไว้เพื่อรองรับกลไกตัวเจนของโมเดลกรณีเป็นภาษาไทยล้วน
            }
        });
    });
</script>
@endpush