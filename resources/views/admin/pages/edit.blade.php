@extends('layouts.admin')

@section('title', 'แก้ไขหน้าเพจอิสระ (Edit Page)')

@push('admin_styles')
<style>
     /* ⚡ [TIER 1] OVERRIDE CKEDITOR 5 INTERNAL CSS VARIABLES */
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
        min-height: 380px !important;
        background-color: #FFFFFF !important;
        color: #1E293B !important;
        padding: 1.25rem !important;
    }

    /* ⚡ [TIER 2] DEEP CSS OVERRIDE (บังคับทุกแท็กย่อยรวม h1-h6 ให้ใช้ Sarabun 100%) */
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
        <div class="d-flex align-items-center mt-2 gap-2">
            <h2 class="h4 mb-0 text-dark fw-bold font-heading">
                <i class="bi bi-pencil-square text-primary me-2"></i>ปรับปรุงหน้าเพจ: <span class="text-primary">{{ $page->title }}</span>
            </h2>
            <span class="badge bg-light text-secondary border font-monospace px-2 py-1">ID: #{{ $page->id }}</span>
        </div>
        <p class="text-muted small font-body mt-1 mb-0">
            ระบบ Audit Logs จะประทับรอยเท้า Data Diffing ทันทีเมื่อมีการกดบันทึก (Google Fonts Sarabun: 18pt / 16pt / 14pt)
        </p>
    </div>

    <!-- 📌 Success / Error Alerts -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4 font-body" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4 font-body" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- 📌 Edit Form Workspace -->
    <form action="{{ route('admin.pages.update', $page->id) }}" method="POST" enctype="multipart/form-data" id="editPageForm">
        @csrf
        @method('PUT')
        
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
                               value="{{ old('title', $page->title) }}" 
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
                                   value="{{ old('slug', $page->slug) }}">
                            @error('slug')
                                <div class="invalid-feedback font-body">{{ $message }}</div>
                            @enderror
                        </div>
                        <small class="text-muted d-block mt-1 font-body" style="font-size: 0.78rem;">
                            * หากแก้ไข Slug ลิ้งก์เดิมที่เคยเผยแพร่อาจเปลี่ยนไปตามชื่อคีย์ใหม่
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
                        <textarea id="ckPageEditor" name="body" class="@error('body') is-invalid @enderror">{!! old('body', $page->body) !!}</textarea>
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
                               placeholder="หากปล่อยว่างระบบจะอิงตามหัวข้อหน้าเพจหลักให้อัตโนมัติ" 
                               value="{{ old('meta_title', $page->meta_title) }}">
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
                                  placeholder="สรุปใจความสำคัญของหน้าเพจอิสระชิ้นนี้ เพื่อเพิ่มอัตราการคลิกเข้าชมของผู้ใช้งาน...">{{ old('meta_description', $page->meta_description) }}</textarea>
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
                    
                    <!-- Secure PDF Attachment & Display -->
                    <div class="mb-4">
                        <label for="secure_pdf" class="form-label small fw-bold text-danger font-heading">
                            <i class="bi bi-file-earmark-pdf me-1"></i> แนบไฟล์เอกสารสำคัญ PDF (ไม่เกิน 30 MB)
                        </label>

                        @if($page->secure_pdf_path)
                            <div class="p-2 bg-danger bg-opacity-10 border border-danger border-opacity-25 rounded-3 mb-2">
                                <div class="d-flex align-items-start gap-2 mb-1">
                                    <i class="bi bi-file-earmark-pdf-fill text-danger fs-5 flex-shrink-0 mt-0.5"></i>
                                    <div class="overflow-hidden w-100">
                                        <span class="d-block small fw-bold text-dark font-body text-truncate" title="{{ basename($page->secure_pdf_path) }}">
                                            {{ basename($page->secure_pdf_path) }}
                                        </span>
                                        <small class="text-muted d-block font-monospace" style="font-size: 0.68rem;">
                                            Path: {{ $page->secure_pdf_path }}
                                        </small>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center pt-1 border-top border-danger border-opacity-10 mt-1">
                                    <span class="badge bg-danger rounded-pill px-2 py-0.5 font-body" style="font-size: 0.65rem;">
                                        <i class="bi bi-shield-lock-fill me-0.5"></i> Secure Vault Protected
                                    </span>
                                </div>
                            </div>
                        @endif

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
                                <i class="bi bi-info-circle text-primary me-1"></i> 
                                หากต้องการเปลี่ยนเอกสาร ให้เลือกไฟล์ PDF ใหม่เพื่ออัปโหลดแทนที่ไฟล์เดิม (ไฟล์เก่าจะถูกลบออกจากดิสก์ออโต้)
                            </small>
                        </div>
                    </div>

                    <!-- Active Switch -->
                    <div class="form-check form-switch mb-4 p-3 bg-light rounded-3 border ms-0 d-flex align-items-center justify-content-between">
                        <label class="form-check-label small fw-bold text-dark font-heading mb-0 ms-1" for="pageActiveSwitch">
                            เปิดให้ออนไลน์หน้าร้านทันที
                        </label>
                        <input type="checkbox" 
                               name="is_active" 
                               class="form-check-input ms-0" 
                               id="pageActiveSwitch" 
                               value="1" 
                               {{ old('is_active', $page->is_active) ? 'checked' : '' }} 
                               style="cursor: pointer; width: 2.2em; height: 1.2em;">
                    </div>

                    <!-- Actions -->
                    <div class="d-flex gap-2 pt-2 border-top">
                        <a href="{{ route('admin.pages.index') }}" class="btn btn-sm btn-secondary w-50 py-2 fw-medium font-heading">
                            ← ยกเลิกคำสั่ง
                        </a>
                        <button type="submit" class="btn btn-sm btn-primary w-50 py-2 shadow-sm fw-bold font-heading">
                            <i class="bi bi-floppy me-1"></i> บันทึกข้อมูลหลัก
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
    });
</script>
@endpush