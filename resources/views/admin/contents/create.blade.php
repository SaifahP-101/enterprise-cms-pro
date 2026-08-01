@extends('layouts.admin')

@section('title', 'เขียนบทความขั้นสูง')

@push('admin_styles')
<style>
    /* ️ สไตล์สำหรับปุ่ม Inline CRUD ภายในกล่อง Select2 Dropdown */
    .select2-results__option { display: flex; justify-content: space-between; align-items: center; padding: 6px 12px; }
    .tag-action-group { display: flex; gap: 4px; opacity: 0.3; transition: opacity 0.2s ease-in-out; }
    .select2-results__option--highlighted .tag-action-group, 
    .select2-results__option:hover .tag-action-group { opacity: 1; }
    .btn-tag-action { padding: 2px 8px; font-size: 0.7rem; border-radius: 4px; border: none; cursor: pointer; font-weight: bold; }
    .btn-tag-edit { background-color: #DAA520; color: #fff; } /* Gilded Gold */
    .btn-tag-delete { background-color: #dc3545; color: #fff; } /* Danger Red */
</style>
@endpush

@section('admin_content')
<div class="container-fluid">
    <div class="mb-4">
        <h2 class="h4 mb-1 text-dark fw-bold"><i class="bi bi-pencil-square"></i> รังสรรค์สารสนเทศทางวัฒนธรรมและกิจกรรม</h2>
        <p class="text-muted small">ผูกระบบแท็กสืบค้นอัจฉริยะ, วิดีโอ YouTube, คลังเอกสารลับ 30MB และตัวเสริมพลัง SEO</p>
    </div>

    <!--  ฟอร์มหลัก: บังคับ multipart/form-data รองรับไฟล์ขนาดใหญ่ -->
    <form action="{{ route('admin.contents.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row g-4">
            
            <!--  ฝั่งซ้าย: ข้อมูลหลัก, CKEditor 5 และแผง SEO -->
            <div class="col-lg-8">
                <div class="card main-content-card p-4 shadow-sm border-0 mb-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">หัวข้อพาดหัวหลักบทความ (Title)</label>
                        <input type="text" name="title" class="form-control shadow-2xs" placeholder="ระบุชื่อเรื่องกิจกรรม/สารสนเทศ..." required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">เนื้อหาข่าวสารเชิงลึก (CKEditor 5 Classic Build)</label>
                        <textarea id="ckContentEditor" name="body"></textarea>
                    </div>
                </div>

                <!--  แผงควบคุม SEO & Open Graph -->
                <div class="card main-content-card p-4 shadow-sm border-0" style="border-top: 4px solid #DAA520;">
                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3"> ส่วนบริหารจัดการคะแนนดันดับการค้นหา (SEO Optimization Panel)</h5>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">หัวข้อเจาะจงการแสดงผลบน Google (Meta Title)</label>
                        <input type="text" name="meta_title" class="form-control form-control-sm shadow-2xs" placeholder="หากปล่อยว่างระบบจะอิงตามหัวข้อพาดหัวหลักด้านบนอัตโนมัติ">
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold text-secondary">คำโปรยเนื้อหาจำลองสั้นเมื่อแชร์ลงโซเชียล (Meta Description)</label>
                        <textarea name="meta_description" class="form-control form-control-sm shadow-2xs" rows="3" placeholder="สรุปใจความสำคัญของเนื้อหา เพื่อจูงใจให้ผู้ใช้งานคลิกกลับเข้ามาชมหน้าเว็บองค์กร..."></textarea>
                    </div>
                </div>
            </div>

            <!--  ฝั่งขวา: การตั้งค่าระบบ, แท็กแบบ Inline CRUD, ไฟล์ PDF ลับ และวิดีโอ -->
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
                                        data-type="{{ $cat->image_type ?? '.JPG .png' }}">
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!--  NEW: แท็กคีย์เวิร์ดพร้อมปุ่มเพิ่มสด (Dynamic Inline CRUD) -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">แท็กคีย์เวิร์ดคัดกรองข้อมูล (Select2 มัลติเซเลกต์)</label>
                        <div class="input-group flex-nowrap">
                            <select name="tags[]" id="inlineCrudSelect2" class="form-select" multiple="multiple" style="width: 75%">
                                @foreach($tags as $tag)
                                    <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-dark fw-bold px-2 shadow-2xs" id="btnQuickCreateTag" style="width: 25%; font-size: 0.8rem;">+ เพิ่มแท็ก</button>
                        </div>
                    </div>

                    <!-- <div class="mb-0">
                        <label class="form-label small fw-bold text-secondary">ประเภทเนื้อหาหลัก</label>
                        <select name="type" class="form-select select2-noclose" style="width: 100%" required>
                            <option value="NEWS" selected> ข่าวประชาสัมพันธ์</option>
                            <option value="ACTIVITY"><i class="bi bi-list-nested"></i> ภาพกิจกรรมบริการวิชาการ</option>
                            <option value="ANNOUNCEMENT"><i class="bi bi-megaphone"></i> ประกาศสำคัญองค์กร</option>
                        </select>
                    </div> -->
                </div>

                <div class="card main-content-card p-4 shadow-sm border-0">
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="bi bi-shield-check"></i> เอกสารปิดลับและมีเดียหลัก</h6>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">รูปหน้าปกบทความ (Cover Image)</label>
                        <input type="file" name="cover_image" class="form-control form-control-sm shadow-3xs" accept="image/*">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-danger"><i class="bi bi-folder"></i> แนบไฟล์เอกสารสำคัญ PDF (ไม่เกิน 30 MB)</label>
                        <input type="file" name="secure_pdf" class="form-control form-control-sm shadow-3xs" accept="application/pdf">
                        <small class="text-muted d-block mt-1">ไฟล์จะถูกเข้ารหัสสตรีมมิ่ง คุ้มครองความปลอดภัยขั้นสูงสุด</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary"> ลิงก์ที่อยู่วิดีโอ YouTube (ถ้ามี)</label>
                        <input type="url" name="youtube_url" class="form-control form-control-sm shadow-3xs" placeholder="https://www.youtube.com/watch?v=...">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">กำหนดวันเวลาเผยแพร่</label>
                        <input type="datetime-local" name="published_at" class="form-control form-control-sm shadow-3xs">
                    </div>

                    <div class="form-check form-switch mb-4">
                        <input type="checkbox" name="is_active" class="form-check-input" id="activeSwitch" checked>
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
    $(document).ready(function() {
        // 1. CKEditor 5
        ClassicEditor.create(document.querySelector('#ckContentEditor'), {
            toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'insertTable', 'blockQuote', 'undo', 'redo']
        }).then(editor => { 
            editor.ui.view.editable.element.style.minHeight = '350px'; 
            editor.ui.view.editable.element.style.backgroundColor = '#FFFFFF';
        }).catch(error => console.error(error));

        // 2. Select2 แกนสากล
        $('.select2-basic').select2({ placeholder: "-- เลือกหมวดหมู่หลัก --", allowClear: true });
        $('.select2-noclose').select2({ minimumResultsForSearch: Infinity });

        // 2.1  Category Image Spec Notice & Required Indicator Handler
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
                $('#coverFileInput').prop('required', true);
            } else {
                $('#categoryImageSpecAlert').slideUp(200);
                $('#coverFileInput').prop('required', false);
            }
        }

        $('#categorySelect').on('change', function() {
            updateCategorySpecNotice();
        });
        updateCategorySpecNotice();

        // 3.  ADVANCED INLINE CRUD TAGS (Select2 Custom Template)
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
            placeholder: " ค้นหาหรือเลือกแท็ก...",
            allowClear: true,
            closeOnSelect: false,
            templateResult: formatTagOption,
            escapeMarkup: function(m) { return m; }
        });

        //  3.1 บันทึกแท็กใหม่ (Create)
        $('#btnQuickCreateTag').click(function(e) {
            e.preventDefault();
            Swal.fire({
                title: ' บันทึกสร้างแท็กข้อมูลใหม่',
                input: 'text',
                inputPlaceholder: 'เช่น สถานที่ตั้ง ลพบุรี...',
                showCancelButton: true,
                confirmButtonColor: '#DAA520', cancelButtonColor: '#202040',
                confirmButtonText: ' บันทึกสด', cancelButtonText: 'ยกเลิก',
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

        //  3.2 แก้ไขแท็ก (Update)
        $(document).on('click', '.action-tag-edit', function(e) {
            e.stopPropagation(); 
            var tagId = $(this).data('id');
            var oldName = $(this).data('name');

            Swal.fire({
                title: ' ปรับปรุงชื่อแท็ก',
                input: 'text', inputValue: oldName,
                showCancelButton: true,
                confirmButtonColor: '#DAA520', cancelButtonColor: '#202040',
                confirmButtonText: ' อัปเดตข้อมูล', cancelButtonText: 'ยกเลิก'
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

        //  3.3 ลบแท็ก (Delete)
        $(document).on('click', '.action-tag-delete', function(e) {
            e.stopPropagation(); 
            var tagId = $(this).data('id');

            Swal.fire({
                title: ' ยืนยันทำลายแท็กนี้?',
                text: "ความสัมพันธ์ของแท็กกับบทความทั้งหมดจะถูกลบทิ้ง!",
                icon: 'warning', showCancelButton: true,
                confirmButtonColor: '#dc3545', cancelButtonColor: '#202040',
                confirmButtonText: ' ลบถาวร', cancelButtonText: 'ยกเลิก'
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