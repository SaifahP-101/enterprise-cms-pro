@extends('layouts.admin')

@section('title', 'สร้างหน้าเพจอิสระใหม่ (Create Page)')

@push('admin_styles')
<style>
    /* ⚡ OVERRIDE CKEDITOR 5 INTERNAL CSS VARIABLES (Preset: Google Fonts Sarabun) */
    :root {
        --ck-font-family: 'Sarabun', sans-serif !important;
    }
 
    .ck.ck-editor,
    .ck-editor__editable,
    .ck-editor__editable_inline,
    .ck-content {
        --ck-font-family: 'Sarabun', sans-serif !important;
        font-family: 'Sarabun', sans-serif !important;
        font-size: 14pt !important;
        line-height: 1.65 !important;
        min-height: 320px !important;
        background-color: #FFFFFF !important;
        color: #1E293B !important;
        padding: 1.25rem !important;
    }

    /* ⚡ DEEP CSS OVERRIDE (บังคับทุกแท็กย่อยรวม h1-h6 ให้ใช้ Sarabun 100%) */
    .ck-editor__editable_inline :is(h1, h2, h3, h4, h5, h6, p, span, a, li, td, th, div, strong, em, blockquote),
    .ck-content :is(h1, h2, h3, h4, h5, h6, p, span, a, li, td, th, div, strong, em, blockquote) {
        font-family: 'Sarabun', sans-serif !important;
    }

    /* 📏 บังคับขนาดฟอนต์มาตรฐานสถาบัน (18pt / 16pt / 14pt) */
    .ck-content :is(h1, h1 *), 
    .ck-editor__editable_inline :is(h1, h1 *) {
        font-size: 18pt !important;
        font-weight: bold !important;
        color: #1E1B4B !important;
        line-height: 1.4 !important;
    }

    .ck-content :is(h2, h2 *), 
    .ck-editor__editable_inline :is(h2, h2 *) {
        font-size: 16pt !important;
        font-weight: bold !important;
        color: #4C1D95 !important;
        line-height: 1.4 !important;
    }

    .ck-content :is(p, p *, li, li *), 
    .ck-editor__editable_inline :is(p, p *, li, li *) {
        font-size: 14pt !important;
        line-height: 1.65 !important;
    }

    .ck-content figure.image img {
        max-width: 100% !important;
        height: auto !important;
        border-radius: 8px;
    }
    
    .ck-content table {
        width: 100% !important;
        border-collapse: collapse !important;
    }
    
    .ck-content table td, .ck-content table th {
        border: 1px solid #CBD5E1 !important;
        padding: 8px 12px !important;
    }

    /* 🎨 Card Header Accent */
    .card-gold-header {
        border-top: 4px solid #DAA520 !important;
    }

    /* 📌 Sticky Panel Management */
    @media (min-width: 992px) {
        .sticky-lg-custom {
            position: sticky;
            top: 85px;
            z-index: 10;
        }
    }
</style>
@endpush

@section('admin_content')
<div class="container-fluid">
    
    <!-- 📌 Header Section -->
    <div class="mb-4">
        <a href="{{ route('admin.pages.index') }}" class="text-decoration-none small text-secondary font-body">
            ← กลับสู่คลังหน้าเพจอิสระ
        </a>
        <h2 class="h4 mb-1 text-dark fw-bold mt-2 font-heading">
            <i class="bi bi-file-earmark-plus text-primary me-2"></i>จัดสร้างหน้าเพจโครงสร้างอิสระชิ้นใหม่
        </h2>
        <p class="text-muted small font-body mb-0">
            ระบบจะทำการแปลงชื่อเรื่องเป็น URL คีย์เส้นทางด่วน (Slug) อัตโนมัติในระดับ Model Lifecycle
        </p>
    </div>

    <!-- 📌 Notification Alerts -->
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4 font-body" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- 📌 Create Form Workspace -->
    <form action="{{ route('admin.pages.store') }}" method="POST" enctype="multipart/form-data" id="createPageForm">
        @csrf
        
        <div class="row g-4">
            
            <!-- 👈 ฝั่งซ้าย: ข้อมูลหลัก, CKEditor 5 และ SEO -->
            <div class="col-lg-8">
                
                <!-- Main Content Card -->
                <div class="card border-0 shadow-sm p-4 rounded-4 bg-white mb-4">
                    <div class="mb-3">
                        <label for="pageTitleInput" class="form-label small fw-bold text-secondary font-heading">
                            หัวข้อหน้าเพจหลัก (Page Title) <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               name="title" 
                               id="pageTitleInput" 
                               class="form-control shadow-none font-body @error('title') is-invalid @enderror" 
                               placeholder="เช่น ประวัติความเป็นมาสำนักฯ, นโยบายความเป็นส่วนตัว..." 
                               value="{{ old('title') }}" 
                               required>
                        @error('title')
                            <div class="invalid-feedback font-body">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="pageSlugInput" class="form-label small fw-bold text-secondary font-heading">
                            กำหนดคีย์เส้นทางด่วนเอง (Custom Slug)
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light small text-muted font-monospace">{{ url('/page') }}/</span>
                            <input type="text" 
                                   name="slug" 
                                   id="pageSlugInput" 
                                   class="form-control shadow-none font-body @error('slug') is-invalid @enderror" 
                                   placeholder="history-and-background" 
                                   value="{{ old('slug') }}">
                            @error('slug')
                                <div class="invalid-feedback font-body">{{ $message }}</div>
                            @enderror
                        </div>
                        <small class="text-muted d-block mt-1 font-body" style="font-size: 0.78rem;">
                            * หากปล่อยว่างไว้ ระบบจะคำนวณเอาชื่อหัวข้อด้านบนมาแปลงเป็น Slug ภาษาไทย/อังกฤษให้อัตโนมัติ
                        </small>
                    </div>

                    <!-- 📝 CKEditor 5 Body Content -->
                    <div class="mb-0">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label small fw-bold text-secondary font-heading mb-0">
                                รายละเอียดเนื้อหาภายในเพจ (CKEditor 5) <span class="text-danger">*</span>
                            </label>
                            <span class="badge bg-warning text-dark font-heading fw-bold px-2 py-1 rounded-pill small">
                                <i class="bi bi-fonts me-1"></i> Preset: Google Fonts Sarabun (18pt / 16pt / 14pt)
                            </span>
                        </div>
                        <textarea id="ckPageEditor" name="body" class="@error('body') is-invalid @enderror">{!! old('body') !!}</textarea>
                        @error('body')
                            <div class="text-danger small font-body mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- SEO Panel -->
                <div class="card card-gold-header border-0 shadow-sm p-4 rounded-4 bg-white">
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3 font-heading">
                        <i class="bi bi-search text-warning me-1"></i> ส่วนบริหารจัดการคะแนนอันดับการค้นหา (SEO Panel)
                    </h6>
                    
                    <div class="mb-3">
                        <label for="meta_title" class="form-label small fw-bold text-secondary font-heading">
                            หัวข้อเจาะจงการแสดงผลบน Google (Meta Title)
                        </label>
                        <input type="text" 
                               name="meta_title" 
                               id="meta_title" 
                               class="form-control form-control-sm shadow-none font-body @error('meta_title') is-invalid @enderror" 
                               placeholder="หากปล่อยว่างระบบจะอิงตามหัวข้อหน้าเพจหลักด้านบนอัตโนมัติ" 
                               value="{{ old('meta_title') }}">
                        @error('meta_title')
                            <div class="invalid-feedback font-body">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-0">
                        <label for="meta_description" class="form-label small fw-bold text-secondary font-heading">
                            คำโปรยเนื้อหาจำลองสั้นเมื่อแชร์ลงโซเชียล (Meta Description)
                        </label>
                        <textarea name="meta_description" 
                                  id="meta_description" 
                                  class="form-control form-control-sm shadow-none font-body @error('meta_description') is-invalid @enderror" 
                                  rows="3" 
                                  placeholder="สรุปใจความสำคัญของหน้าเพจอิสระชิ้นนี้ เพื่อเพิ่มประสิทธิภาพการคลิกของบอทค้นหา...">{{ old('meta_description') }}</textarea>
                        @error('meta_description')
                            <div class="invalid-feedback font-body">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

            </div>

            <!-- 👉 ฝั่งขวา: การเผยแพร่, เอกสาร PDF และปุ่มคำสั่ง -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm p-4 rounded-4 bg-white sticky-lg-custom">
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3 font-heading">
                        <i class="bi bi-gear text-primary me-1"></i> การเผยแพร่และระบบป้องกันสิทธิ์
                    </h6>
                    
                    <!-- Secure PDF Attachment -->
                    <div class="mb-4">
                        <label for="secure_pdf" class="form-label small fw-bold text-danger font-heading">
                            <i class="bi bi-file-earmark-pdf me-1"></i> แนบไฟล์เอกสารสำคัญ PDF (ไม่เกิน 30 MB)
                        </label>
                        <input type="file" 
                               name="secure_pdf" 
                               id="secure_pdf" 
                               class="form-control form-control-sm shadow-none font-body @error('secure_pdf') is-invalid @enderror" 
                               accept="application/pdf">
                        @error('secure_pdf')
                            <div class="invalid-feedback font-body">{{ $message }}</div>
                        @enderror
                        
                        <div class="p-2 bg-light rounded-3 border mt-2">
                            <small class="text-muted d-block lh-sm font-body" style="font-size: 0.75rem;">
                                <i class="bi bi-shield-check text-success me-1"></i> 
                                ไฟล์นี้จะถูกจัดเก็บเข้าสู่ <strong>Secure Vault Folder</strong> สตรีมมิ่งผ่านรหัสไบนารีหน้าบ้าน และล็อกการคลิกขวา/ห้ามสั่งปริ๊นท์อัตโนมัติ
                            </small>
                        </div>
                    </div>

                    <!-- Active Switch -->
                    <div class="form-check form-switch mb-4 p-3 bg-light rounded-3 border ms-0 d-flex align-items-center justify-content-between">
                        <label class="form-check-label small fw-bold text-success font-heading mb-0 ms-1" for="pageActiveSwitch">
                            เปิดให้ออนไลน์หน้าร้านทันที
                        </label>
                        <input type="checkbox" 
                               name="is_active" 
                               class="form-check-input ms-0" 
                               id="pageActiveSwitch" 
                               value="1" 
                               {{ old('is_active', true) ? 'checked' : '' }} 
                               style="cursor: pointer; width: 2.2em; height: 1.2em;">
                    </div>

                    <!-- Actions -->
                    <div class="d-flex gap-2 pt-2 border-top">
                        <a href="{{ route('admin.pages.index') }}" class="btn btn-sm btn-secondary w-50 py-2 fw-medium font-heading">
                            ← ย้อนกลับ
                        </a>
                        <button type="submit" class="btn btn-sm btn-success w-50 py-2 shadow-sm fw-bold font-heading">
                            <i class="bi bi-floppy me-1"></i> บันทึกติดตั้งเพจ
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>
@endsection

@push('admin_scripts')
<script>
    // 🛡️ Safe Script Loader: ป้องกันข้อผิดพลาด ckeditor-duplicated-modules
    function loadCKEditorSafely(callback) {
        if (typeof window.ClassicEditor !== 'undefined') {
            callback();
            return;
        }

        let existingScript = document.querySelector('script[src*="ckeditor.js"]');
        if (existingScript) {
            existingScript.addEventListener('load', callback);
        } else {
            let script = document.createElement('script');
            script.src = "{{ asset('vendor/ckeditor5/ckeditor.js') }}";
            script.onload = callback;
            document.head.appendChild(script);
        }
    }

    $(document).ready(function() {
        // 1. 🔤 CKEditor 5 Initialization with Sarabun Presets (18pt / 16pt / 14pt)
        loadCKEditorSafely(function() {
            if (document.querySelector('#ckPageEditor') && !document.querySelector('.ck-editor')) {
                ClassicEditor.create(document.querySelector('#ckPageEditor'), {
                    fontFamily: {
                        options: [
                            'Sarabun, sans-serif',
                            'Kanit, sans-serif',
                            'Arial, Helvetica, sans-serif'
                        ],
                        supportAllValues: true
                    },
                    fontSize: {
                        options: [
                            '14pt',
                            '16pt',
                            '18pt',
                            'default'
                        ],
                        supportAllValues: true
                    },
                    heading: {
                        options: [
                            { model: 'paragraph', title: 'เนื้อหาหลัก (14pt)', class: 'ck-heading_paragraph' },
                            { model: 'heading1', view: 'h1', title: 'หัวข้อหลัก (18pt)', class: 'ck-heading_heading1' },
                            { model: 'heading2', view: 'h2', title: 'หัวข้อรอง (16pt)', class: 'ck-heading_heading2' }
                        ]
                    },
                    toolbar: [
                        'heading', '|',
                        'fontFamily', 'fontSize', 'fontColor', 'fontBackgroundColor', '|',
                        'bold', 'italic', 'underline', 'strikethrough', '|',
                        'alignment', 'bulletedList', 'numberedList', '|',
                        'link', 'insertTable', 'blockQuote', 'undo', 'redo'
                    ]
                }).then(editor => { 
                    // ⚡ สั่งฉีด Inline Style ลงใน Root View ของ Editor โดยตรง
                    editor.editing.view.change(writer => {
                        let root = editor.editing.view.document.getRoot();
                        writer.setStyle('font-family', "'Sarabun', sans-serif", root);
                        writer.setStyle('font-size', '14pt', root);
                    });
                }).catch(error => console.error('CKEditor Init Error:', error));
            }
        });

        // 2. Slug Auto-cleaner
        $('#pageTitleInput').on('input', function() {
            var rawText = $(this).val();
            var cleanedSlug = rawText.toLowerCase()
                                     .replace(/[^a-z0-9ก-๙\s-]/g, '')
                                     .replace(/\s+/g, '-');
        });
    });
</script>
@endpush