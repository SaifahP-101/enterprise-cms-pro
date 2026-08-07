@extends('layouts.admin')

@section('title', 'ถังขยะกู้คืนบทความ')

@section('admin_content')
<div class="container-fluid animate__animated animate__fadeIn">
    <!-- ส่วนหัวแผงควบคุมหลัก (Header Section) -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="h4 mb-1 text-dark fw-bold"><i class="bi bi-trash3"></i> ถังขยะและศูนย์กู้คืนคลังบทความ (Content Trash Vault)</h2>
            <p class="text-muted small mb-0">กู้คืนสารสนเทศทางวัฒนธรรมที่ถูกลบชั่วคราว หรือสั่งกวาดล้างทำลายไฟล์แบบถาวรออกจากเซิร์ฟเวอร์</p>
        </div>
        <a href="{{ route('admin.contents.index') }}" class="btn btn-secondary shadow-sm fw-bold px-4 py-2">
            ← กลับสู่ระบบคลังบทความปกติ
        </a>
    </div>

    <!-- แผงแจ้งเตือนผลธุรกรรม (Session Alert Flash) -->
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show" role="alert" style="border-left: 4px solid #198754 !important;">
            <span class="fw-medium"><i class="bi bi-floppy"></i> สำเร็จ:</span> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- กระดานตารางรายการบทความในถังขยะ (Trashed Records Board) -->
    <div class="card main-content-card border-0 shadow-sm overflow-hidden" style="border-top: 3px solid #0dcaf0 !important;">
        <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
            <h5 class="m-0 fw-bold text-dark" style="color: var(--theme-indigo) !important;"><i class="bi bi-clipboard"></i> รายการบทความที่อยู่ระหว่างรอการกู้คืนหรือทำลาย</h5>
            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-3 py-2 rounded-pill fw-bold">
                รวมทั้งหมด {{ count($trashedContents) }} รายการ
            </span>
        </div>
        
        <div class="table-responsive style-scrollbar">
            <table class="table table-hover align-middle mb-0 text-nowrap">
                <thead class="bg-light text-secondary fw-bold" style="font-size: 0.85rem;">
                    <tr>
                        <th width="70" class="text-center py-3 ps-4">ID</th>
                        <th width="120" class="text-center py-3">ภาพปกเดิม</th>
                        <th class="py-3">ชื่อพาดหัวหลักบทความ / สังกัดหมวดหมู่</th> 
                        <th width="180" class="text-center py-3">⏳ วันเวลาที่ถูกลบ</th>
                        <th width="200" class="text-center py-3 pe-4">การปฏิบัติการกู้คืนข้อมูล</th>
                    </tr>
                </thead>
                <tbody class="border-top-0" style="font-size: 0.9rem;">
                    @forelse($trashedContents as $content)
                    <tr>
                        <!-- 1. ไอดีบทความ -->
                        <td class="text-center py-3 ps-4 fw-bold text-secondary bg-light-subtle">{{ $content->id }}</td>
                        
                        <!-- 2. ภาพปกตัวอย่างเดิม -->
                        <td class="text-center py-3">
                            <div class="d-inline-block rounded border p-1 bg-white shadow-3xs" style="width: 80px; height: 50px; overflow: hidden;">
                                @if($content->cover_image)
                                    <img src="{{ asset('storage/' . $content->cover_image) }}" class="w-100 h-100 rounded" style="object-fit: cover; object-position: center;">
                                @else
                                    <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted bg-light" style="font-size: 0.65rem;">NO IMAGE</div>
                                @endif
                            </div>
                        </td>
                        
                        <!-- 3. ชื่อพาดหัวและหมวดหมู่ -->
                        <td class="py-3">
                            <span class="d-block fw-bold text-dark mb-1 text-truncate" style="max-width: 380px;">{{ $content->title }}</span>
                            <span class="small text-muted d-inline-flex align-items-center gap-1">
                                <i class="bi bi-folder"></i> หมวดหมู่เดิม: <strong class="text-secondary">{{ $content->category->name ?? 'ไม่ระบุ' }}</strong>
                            </span>
                        </td>
                         
                        <!-- 5. วันเวลาที่ถูกกวาดล้างลงถังขยะ -->
                        <td class="text-center py-3 small bg-light-subtle text-danger fw-medium">
                            {{ $content->deleted_at ? $content->deleted_at->format('d/m/Y H:i') : 'ไม่ระบุ' }} น.
                        </td>
                        
                        <!-- 6. ปุ่มคำสั่งกู้คืนความปลอดภัยหรือทำลายทิ้งถาวร -->
                        <td class="text-center py-3 pe-4">
                            <div class="d-inline-flex gap-2">
                                <!-- ปุ่มยิงคำสั่งส่งสัญญาณกู้คืนข้อมูล (Restore Engine) -->
                                <form action="{{ route('admin.contents.restore', $content->id) }}" method="POST" class="d-inline form-restore-action">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-outline-success fw-bold px-3 d-flex align-items-center gap-1">
                                        <i class="bi bi-arrow-clockwise"></i> กู้คืนข้อมูล
                                    </button>
                                </form>

                                <!-- ปุ่มยิงคำสั่งทำลายทิ้งแบบถาวร (Force Delete Engine) -->
                                <form action="{{ route('admin.contents.force_delete', $content->id) }}" method="POST" class="d-inline form-force-delete-action">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger fw-bold px-3 d-flex align-items-center gap-1">
                                         ทำลายถาวร
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5 bg-light-subtle">
                            <div class="py-4">
                                <span class="fs-1 d-block mb-2"></span>
                                <span class="fw-medium text-secondary">ถังขยะว่างเปล่า ไม่มีบทความสารสนเทศตกค้างอยู่ในระบบคลัง</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('admin_scripts')
<script>
    $(document).ready(function() {
        //  1. ระบบรักษาความปลอดภัยดักจับคำสั่งกู้คืนข้อมูลบทความ (Restore Safety Gate)
        $('.form-restore-action').on('submit', function(e) {
            e.preventDefault();
            var executionForm = this;
            Swal.fire({
                title: ' ยืนยันกู้คืนบทความสารสนเทศ?',
                text: "บทความ ข้อมูลคะแนน SEO และสิทธิ์เอกสารจะกลับไปออนไลน์และเผยแพร่หน้าร้านตามปกติ!",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#202040',
                confirmButtonText: ' ใช่, สั่งกู้คืนทันที',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    executionForm.submit();
                }
            });
        });

        //  2. ระบบรักษาความปลอดภัยดักจับคำสั่งทำลายทิ้งถาวร (Force Delete Safety Gate)
        $('.form-force-delete-action').on('submit', function(e) {
            e.preventDefault();
            var destructionForm = this;
            Swal.fire({
                title: '️️️ สั่งทำลายบทความแบบถาวร?',
                text: "คำเตือนสูงสุด: เรคคอร์ดในฐานข้อมูล ไฟล์รูปภาพปก และเอกสาร PDF ลับในระบบ Storage จะถูกลบทิ้งแบบถอนรากถอนโคน ไม่สามารถกู้คืนได้อีกต่อไป!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#202040',
                confirmButtonText: ' ยืนยันทำลายถาวร',
                cancelButtonText: 'รักษาไฟล์ไว้'
            }).then((result) => {
                if (result.isConfirmed) {
                    destructionForm.submit();
                }
            });
        });
    });
</script>
@endpush