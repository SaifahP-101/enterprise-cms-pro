@extends('layouts.admin')

@section('title', 'แก้ไขหน้าเพจอิสระ')

@section('admin_content')
<div class="container-fluid animate__animated animate__fadeIn">
    <div class="mb-4">
        <a href="{{ route('admin.pages.index') }}" class="text-decoration-none small text-secondary">← กลับสู่คลังหน้าเพจอิสระ</a>
        <h2 class="h4 mb-1 text-dark fw-bold mt-2"><i class="bi bi-pencil-square"></i> ปรับปรุงแก้ไขหน้าเพจอิสระ: <span class="text-primary">{{ $page->title }}</span></h2>
        <p class="text-muted small">การบันทึกแก้ไขในหน้านี้จะถูกบันทึกลงฐานข้อมูลประวัติ Audit Logs Trait ความปลอดภัยข้ามเขตไซต์</p>
    </div>

    <form action="{{ route('admin.pages.update', $page->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="row g-4">
            
            <!--  ฝั่งซ้าย: ข้อมูลหลักตัวเพจอิสระเดิม -->
            <div class="col-lg-8">
                <div class="card main-content-card p-4 shadow-sm border-0 mb-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">หัวข้อหน้าเพจหลัก (Page Title) *</label>
                        <input type="text" name="title" class="form-control shadow-2xs" value="{{ $page->title }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">คีย์เส้นทางด่วนสำหรับแชร์ (Slug Path)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light small text-muted">{{ url('/page') }}/</span>
                            <input type="text" name="slug" class="form-control shadow-2xs" value="{{ $page->slug }}" placeholder="ลิงก์ปลายทางด่วน...">
                        </div>
                        <div class="form-text text-warning small">️ ข้อควรระวัง: การเปลี่ยนชื่อคีย์ Slug อาจส่งผลให้ลิงก์เก่าที่เคยนำไปวางข้างนอกเข้าใช้งานไม่ได้</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">รายละเอียดเนื้อหาภายในเพจ (CKEditor 5) *</label>
                        <textarea id="ckPageEditor" name="body">{!! $page->body !!}</textarea>
                    </div>
                </div>

                <!--  แผงควบคุม SEO สำหรับอัปเดตค่า -->
                <div class="card main-content-card p-4 shadow-sm border-0" style="border-top: 4px solid #DAA520;">
                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3"> ส่วนบริหารจัดการคะแนนอันดับการค้นหา (SEO Panel)</h5>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Meta Title</label>
                        <input type="text" name="meta_title" class="form-control form-control-sm shadow-2xs" value="{{ $page->meta_title }}">
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold text-secondary">Meta Description</label>
                        <textarea name="meta_description" class="form-control form-control-sm shadow-2xs" rows="3">{{ $page->meta_description }}</textarea>
                    </div>
                </div>
            </div>

            <!--  ฝั่งขวา: การควบคุมเอกสารปิดลับสตรีมมิ่ง -->
            <div class="col-lg-4">
                <div class="card main-content-card p-4 shadow-sm border-0 position-sticky" style="top: 20px;">
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="bi bi-gear"></i> การเผยแพร่และระบบป้องกันสิทธิ์</h6>
                    
                    <!--  Secure PDF Attachment Current State Check -->
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-danger"><i class="bi bi-folder"></i> อัปเดตไฟล์เอกสารสำคัญ PDF (ไม่เกิน 30 MB)</label>
                        
                        @if($page->secure_pdf_path)
                            <div class="mb-3 p-2.5 bg-light rounded-3 border d-flex justify-content-between align-items-center shadow-3xs">
                                <span class="small text-truncate text-success fw-medium" style="max-width: 170px;"><i class="bi bi-file-earmark-text"></i> มีเอกสารลับคุ้มครองอยู่</span>
                                <a href="{{ route('secure.pdf.stream', ['filename' => $page->secure_pdf_path]) }}" target="_blank" class="btn btn-xs btn-dark px-2.5 py-1 text-white fw-bold" style="font-size: 0.7rem;">เปิดตรวจอ่าน</a>
                            </div>
                        @endif

                        <input type="file" name="secure_pdf" class="form-control form-control-sm shadow-3xs" accept="application/pdf">
                        <small class="text-muted d-block mt-2 lh-base">อัปโหลดไฟล์ชิ้นใหม่เข้ามาเฉพาะเมื่อต้องการเปลี่ยนรูปหรือล้างไฟล์เอกสารลับชุดเก่า</small>
                    </div>

                    <!-- สถานะสวิตช์เปิด-ปิดการออนแอร์เพจอิสระ -->
                    <div class="form-check form-switch mb-4 p-3 bg-light rounded border ms-0">
                        <input type="checkbox" name="is_active" class="form-check-input ms-0 me-2" id="pageActiveSwitch" {{ $page->is_active ? 'checked' : '' }}>
                        <label class="form-check-label small fw-bold text-success" for="pageActiveSwitch">เปิดการแสดงผลสู่หน้าร้านปกติ</label>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.pages.index') }}" class="btn btn-sm btn-secondary w-50 py-2 fw-medium">← ยกเลิกคำสั่ง</a>
                        <button type="submit" class="btn btn-sm btn-primary w-50 py-2 shadow-sm fw-bold" style="background-color: var(--theme-indigo); border-color: var(--theme-indigo);"><i class="bi bi-floppy"></i> อัปเดตข้อมูลเพจ</button>
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
        ClassicEditor.create(document.querySelector('#ckPageEditor'), {
            toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'insertTable', 'blockQuote', 'undo', 'redo']
        }).then(editor => { 
            editor.ui.view.editable.element.style.minHeight = '380px'; 
            editor.ui.view.editable.element.style.backgroundColor = '#FFFFFF';
        }).catch(error => console.error(error));
    });
</script>
@endpush