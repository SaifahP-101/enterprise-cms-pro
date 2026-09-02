@extends('layouts.admin')

@section('title', 'เขียนบทความขั้นสูง')

@push('admin_styles')
<!-- Fonts already loaded from vendor/fonts.css in admin layout -->

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

    /* 🏷️ สไตล์สำหรับปุ่ม Inline CRUD ภายในกล่อง Select2 Dropdown */
    .select2-results__option { display: flex; justify-content: space-between; align-items: center; padding: 6px 12px; }
    .tag-action-group { display: flex; gap: 4px; opacity: 0.3; transition: opacity 0.2s ease-in-out; }
    .select2-results__option--highlighted .tag-action-group, 
    .select2-results__option:hover .tag-action-group { opacity: 1; }
    .btn-tag-action { padding: 2px 8px; font-size: 0.7rem; border-radius: 4px; border: none; cursor: pointer; font-weight: bold; }
    .btn-tag-edit { background-color: #DAA520; color: #fff; }
    .btn-tag-delete { background-color: #dc3545; color: #fff; }
</style>
@endpush

@section('admin_content')
<div class="container-fluid">
    <div class="mb-4">
        <h2 class="h4 mb-1 text-dark fw-bold"><i class="bi bi-pencil-square"></i> รังสรรค์สารสนเทศทางวัฒนธรรมและกิจกรรม</h2>
        <p class="text-muted small">ผูกระบบแท็กสืบค้นอัจฉริยะ, วิดีโอ YouTube, คลังเอกสารลับ 30MB และตัวเสริมพลัง SEO (Google Fonts Sarabun: 18pt / 16pt / 14pt)</p>
    </div>

    <!-- 📝 ฟอร์มหลัก -->
    <form action="{{ route('admin.contents.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row g-4">
            
            <!-- 👈 ฝั่งซ้าย: ข้อมูลหลัก, CKEditor 5 และแผง SEO -->
            <div class="col-lg-8">
                <div class="card main-content-card p-4 shadow-sm border-0 mb-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">หัวข้อพาดหัวหลักบทความ (Title) <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control shadow-2xs" placeholder="ระบุชื่อเรื่องกิจกรรม/สารสนเทศ..." value="{{ old('title') }}" required>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label small fw-bold text-secondary mb-0">เนื้อหาข่าวสารเชิงลึก (CKEditor 5 Classic Build)</label>
                            <span class="badge bg-warning text-dark font-heading fw-bold px-2.5 py-1 rounded-pill small">
                                <i class="bi bi-fonts me-1"></i> Preset: Google Fonts Sarabun (18pt / 16pt / 14pt)
                            </span>
                        </div>
                        <textarea id="ckContentEditor" name="body">{!! old('body') !!}</textarea>
                    </div>
                </div>

                <!-- 🚀 แผงควบคุม SEO & Open Graph -->
                <div class="card main-content-card p-4 shadow-sm border-0" style="border-top: 4px solid #DAA520;">
                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">ส่วนบริหารจัดการคะแนนดันดับการค้นหา (SEO Optimization Panel)</h5>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">หัวข้อเจาะจงการแสดงผลบน Google (Meta Title)</label>
                        <input type="text" name="meta_title" class="form-control form-control-sm shadow-2xs" placeholder="หากปล่อยว่างระบบจะอิงตามหัวข้อพาดหัวหลักด้านบนอัตโนมัติ" value="{{ old('meta_title') }}">
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold text-secondary">คำโปรยเนื้อหาจำลองสั้นเมื่อแชร์ลงโซเชียล (Meta Description)</label>
                        <textarea name="meta_description" class="form-control form-control-sm shadow-2xs" rows="3" placeholder="สรุปใจความสำคัญของเนื้อหา เพื่อจูงใจให้ผู้ใช้งานคลิกกลับเข้ามาชมหน้าเว็บองค์กร...">{{ old('meta_description') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- 👉 ฝั่งขวา: การตั้งค่าระบบ, แท็กแบบ Inline CRUD, ไฟล์ PDF ลับ และวิดีโอ -->
            <div class="col-lg-4">
                <div class="card main-content-card p-4 mb-4 shadow-sm border-0">
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="bi bi-gear"></i> การคัดแยกและระบบแท็กภูมิปัญญา</h6>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">สังกัดหมวดหมู่หลัก (Select2) <span class="text-danger">*</span></label>
                        <select name="category_id" id="categorySelect" class="form-select select2-basic" style="width: 100%" required>
                            <option value=""></option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}"
                                        data-dimension="{{ $cat->image_dimension ?? '1200 x 630 Pixels' }}"
                                        data-size="{{ $cat->image_size ?? '2MB' }}"
                                        data-type="{{ $cat->image_type ?? '.JPG .png' }}"
                                        {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 🔖 แท็กคีย์เวิร์ดพร้อมปุ่มเพิ่มสด (Dynamic Inline CRUD) -->
                    <div class="mb-0">
                        <label class="form-label small fw-bold text-secondary">แท็กคีย์เวิร์ดคัดกรองข้อมูล (Select2 มัลติเซเลกต์)</label>
                        <div class="input-group flex-nowrap">
                            <select name="tags[]" id="inlineCrudSelect2" class="form-select" multiple="multiple" style="width: 75%">
                                @foreach($tags as $tag)
                                    <option value="{{ $tag->id }}" {{ is_array(old('tags')) && in_array($tag->id, old('tags')) ? 'selected' : '' }}>
                                        {{ $tag->name }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-dark fw-bold px-2 shadow-2xs" id="btnQuickCreateTag" style="width: 25%; font-size: 0.8rem;">+ เพิ่มแท็ก</button>
                        </div>
                    </div>
                </div>

                <div class="card main-content-card p-4 shadow-sm border-0">
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="bi bi-shield-check"></i> เอกสารปิดลับและมีเดียหลัก</h6>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">รูปหน้าปกบทความ (Cover Image)</label>
                        <input type="file" name="cover_image" id="coverFileInput" class="form-control form-control-sm shadow-3xs" accept="image/*">
                        
                        <div id="categoryImageSpecAlert" class="mt-2 p-2.5 rounded border border-warning border-opacity-50 bg-warning bg-opacity-10 small shadow-3xs" style="display: none;">
                            <span class="fw-bold text-dark d-block mb-1"><i class="bi bi-info-circle-fill text-warning me-1"></i> ข้อกำหนดรูปหน้าปกประจำหมวดหมู่ที่เลือก:</span>
                            <div class="d-flex flex-wrap gap-1.5 mt-1">
                                <span class="badge bg-white text-dark border fw-semibold px-2 py-1" id="specDimension"><i class="bi bi-aspect-ratio me-1 text-primary"></i></span>
                                <span class="badge bg-white text-dark border fw-semibold px-2 py-1" id="specSize"><i class="bi bi-hdd me-1 text-success"></i></span>
                                <span class="badge bg-white text-dark border fw-semibold px-2 py-1" id="specType"><i class="bi bi-filetype-png me-1 text-danger"></i></span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-danger"><i class="bi bi-folder"></i> แนบไฟล์เอกสารสำคัญ PDF (ไม่เกิน 30 MB)</label>
                        <input type="file" name="secure_pdf" class="form-control form-control-sm shadow-3xs" accept="application/pdf">
                        <small class="text-muted d-block mt-1">ไฟล์จะถูกเข้ารหัสสตรีมมิ่ง คุ้มครองความปลอดภัยขั้นสูงสุด</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">ลิงก์ที่อยู่วิดีโอ YouTube (ถ้ามี)</label>
                        <input type="url" name="youtube_url" class="form-control form-control-sm shadow-3xs" placeholder="https://www.youtube.com/watch?v=..." value="{{ old('youtube_url') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">กำหนดวันเวลาเผยแพร่</label>
                        <input type="datetime-local" name="published_at" class="form-control form-control-sm shadow-3xs" value="{{ old('published_at', now()->format('Y-m-d\TH:i')) }}">
                    </div>

                    <div class="form-check form-switch mb-4">
                        <input type="checkbox" name="is_active" class="form-check-input" id="activeSwitch" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                        <label class="form-check-label small fw-bold text-success" for="activeSwitch">อนุมัติการเผยแพร่ทันที</label>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.contents.index') }}" class="btn btn-sm btn-secondary w-50 py-2 fw-medium">← ย้อนกลับ</a>
                        <button type="submit" class="btn btn-sm btn-success w-50 py-2 shadow-sm fw-bold"><i class="bi bi-floppy"></i> ยืนยันร่างข้อมูล</button>
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
        // 1. 🔤 [TIER 3 JS FIX] CKEditor 5 Initialization with Explicit View Writer Enforcer
        loadCKEditorSafely(function() {
            if (document.querySelector('#ckContentEditor') && !document.querySelector('.ck-editor')) {
                ClassicEditor.create(document.querySelector('#ckContentEditor'), {
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

        // 2. Select2 สากล
        $('.select2-basic').select2({ placeholder: "-- เลือกหมวดหมู่หลัก --", allowClear: true });
        $('.select2-noclose').select2({ minimumResultsForSearch: Infinity });

        // 2.1 Category Image Spec Notice Handler
        function updateCategorySpecNotice() {
            var selectedOption = $('#categorySelect option:selected');
            var dimension = selectedOption.data('dimension');
            var size = selectedOption.data('size');
            var type = selectedOption.data('type');

            if (selectedOption.val() && (dimension || size || type)) {
                $('#specDimension').html('<i class="bi bi-aspect-ratio text-primary me-1"></i> ขนาดภาพ: ' + (dimension || '1200 x 630 Pixels'));
                $('#specSize').html('<i class="bi bi-hdd text-success me-1"></i> ขนาดไฟล์สูงสุด: ' + (size || '2MB'));
                $('#specType').html('<i class="bi bi-filetype-png text-danger me-1"></i> ชนิดไฟล์: ' + (type || '.JPG .png'));
                $('#categoryImageSpecAlert').slideDown(200);
            } else {
                $('#categoryImageSpecAlert').slideUp(200);
            }
        }

        $('#categorySelect').on('change', function() {
            updateCategorySpecNotice();
        });
        updateCategorySpecNotice();

        // 3. ADVANCED INLINE CRUD TAGS
        function formatTagOption(state) {
            if (!state.id) { return state.text; }
            var $state = $(
                '<span>' + state.text + '</span>' +
                '<div class="tag-action-group">' +
                    '<button type="button" class="btn-tag-action btn-tag-edit action-tag-edit" data-id="'+state.id+'" data-name="'+state.text+'"></button>' +
                    '<button type="button" class="btn-tag-action btn-tag-delete action-tag-delete" data-id="'+state.id+'"></button>' +
                '</div>'
            );
            return $state;
        }

        var $select2Element = $('#inlineCrudSelect2').select2({
            placeholder: "ค้นหาหรือเลือกแท็ก...",
            allowClear: true,
            closeOnSelect: false,
            templateResult: formatTagOption,
            escapeMarkup: function(m) { return m; }
        });

        // 3.1 บันทึกแท็กใหม่
        $('#btnQuickCreateTag').click(function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'บันทึกสร้างแท็กข้อมูลใหม่',
                input: 'text',
                inputPlaceholder: 'เช่น สถานที่ตั้ง ลพบุรี...',
                showCancelButton: true,
                confirmButtonColor: '#DAA520', cancelButtonColor: '#202040',
                confirmButtonText: 'บันทึกสด', cancelButtonText: 'ยกเลิก',
                inputValidator: (value) => { if (!value) return 'กรุณากรอกข้อความชื่อแท็ก!' }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('admin.tags.ajax_store') }}",
                        type: "POST",
                        data: { name: result.value, _token: "{{ csrf_token() }}" },
                        success: function(res) {
                            var newOption = new Option(res.name, res.id, true, true);
                            $select2Element.append(newOption).trigger('change');
                            Swal.fire({ icon: 'success', title: 'เพิ่มแท็กสำเร็จ!', timer: 1000, showConfirmButton: false });
                        }
                    });
                }
            });
        });

        // 3.2 แก้ไขแท็ก
        $(document).on('click', '.action-tag-edit', function(e) {
            e.stopPropagation(); 
            var tagId = $(this).data('id');
            var oldName = $(this).data('name');

            Swal.fire({
                title: 'ปรับปรุงชื่อแท็ก',
                input: 'text', inputValue: oldName,
                showCancelButton: true,
                confirmButtonColor: '#DAA520', cancelButtonColor: '#202040',
                confirmButtonText: 'อัปเดตข้อมูล', cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    $.ajax({
                        url: "/admin/tags/ajax-update/" + tagId,
                        type: "PUT",
                        data: { name: result.value, _token: "{{ csrf_token() }}" },
                        success: function(res) {
                            $select2Element.find('option[value="' + tagId + '"]').text(res.name);
                            $select2Element.trigger('change');
                            Swal.fire({ icon: 'success', title: 'แก้ไขสำเร็จ!', timer: 1000, showConfirmButton: false });
                        }
                    });
                }
            });
        });

        // 3.3 ลบแท็ก
        $(document).on('click', '.action-tag-delete', function(e) {
            e.stopPropagation(); 
            var tagId = $(this).data('id');

            Swal.fire({
                title: 'ยืนยันทำลายแท็กนี้?',
                text: "ความสัมพันธ์ของแท็กกับบทความทั้งหมดจะถูกลบทิ้ง!",
                icon: 'warning', showCancelButton: true,
                confirmButtonColor: '#dc3545', cancelButtonColor: '#202040',
                confirmButtonText: 'ลบถาวร', cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "/admin/tags/ajax-destroy/" + tagId,
                        type: "DELETE",
                        data: { _token: "{{ csrf_token() }}" },
                        success: function(res) {
                            $select2Element.find('option[value="' + tagId + '"]').remove();
                            $select2Element.trigger('change');
                            Swal.fire({ icon: 'success', title: 'กวาดล้างแท็กเรียบร้อย!', timer: 1000, showConfirmButton: false });
                        }
                    });
                }
            });
        });
    });
</script>
@endpush