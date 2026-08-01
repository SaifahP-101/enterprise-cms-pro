@extends('layouts.admin')

@section('title', 'จัดการแบนเนอร์สไลด์โชว์')

@section('admin_content')
<div class="container-fluid animate__animated animate__fadeIn">
    <!-- ส่วนหัวแผงควบคุมหลัก -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="h4 mb-1 text-dark fw-bold"><i class="bi bi-images"></i> ระบบจัดสรรแบนเนอร์สไลด์โชว์หน้าแรก</h2>
            <p class="text-muted small mb-0">ปรับแต่งคลังมีเดียประชาสัมพันธ์ คุมประสิทธิภาพการสืบค้นผ่านกลไก Composite Index</p>
        </div>
        <button class="btn btn-primary shadow-sm fw-bold px-4 py-2" data-bs-toggle="modal" data-bs-target="#createSlideModal" style="background-color: var(--theme-indigo); border-color: var(--theme-indigo);">
            <i class="bi bi-sparkles"></i> เพิ่มแบนเนอร์ใหม่
        </button>
    </div>

    <!-- แผงแจ้งเตือนการทำงาน (Alert Notifications System) -->
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show" role="alert" style="border-left: 4px solid #198754 !important;">
            <span class="fw-medium"><i class="bi bi-floppy"></i> สำเร็จ:</span> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm" style="border-left: 4px solid #dc3545 !important;">
            <span class="fw-bold d-block mb-1">️ ตรวจพบข้อผิดพลาดระบบกรอกข้อมูล:</span>
            <ul class="mb-0 ps-3 small">
                @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    @endif

    <!-- กระดานตารางรายการสารสนเทศ (Responsive Data Board) -->
    <div class="card main-content-card border-0 shadow-sm overflow-hidden">
        <div class="card-header bg-white border-0 py-3 px-4">
            <h5 class="m-0 fw-bold text-dark" style="color: var(--theme-indigo) !important;"><i class="bi bi-clipboard"></i> รายการแบนเนอร์สไลด์ปัจจุบันในคลังระบบ</h5>
        </div>
        <div class="table-responsive style-scrollbar">
            <table class="table table-hover align-middle mb-0 text-nowrap">
                <thead class="bg-light text-secondary fw-bold" style="font-size: 0.85rem;">
                    <tr>
                        <th width="80" class="text-center py-3 ps-4">ลำดับโชว์</th>
                        <th width="180" class="text-center py-3">ภาพตัวอย่าง</th>
                        <th class="py-3">รายละเอียดสารัตถะข่าวสาร</th>
                        <th width="120" class="text-center py-3">สถานะระบบ</th>
                        <th width="160" class="text-center py-3 pe-4">การจัดการคลังไฟล์</th>
                    </tr>
                </thead>
                <tbody class="border-top-0" style="font-size: 0.9rem;">
                    @forelse($slideshows as $slide)
                    <tr>
                        <td class="text-center py-3 ps-4 fw-bold text-dark bg-light-subtle">
                            <span class="badge rounded-pill px-3 py-2 bg-secondary bg-opacity-10 text-dark border">{{ $slide->sort_order }}</span>
                        </td>
                        <td class="text-center py-3">
                            <div class="d-inline-block rounded border p-1 bg-white shadow-3xs" style="width: 140px; height: 75px; overflow: hidden;">
                                <img src="{{ asset('storage/' . $slide->image_path) }}" class="w-100 h-100 rounded" style="object-fit: cover; object-position: center;">
                            </div>
                        </td>
                        <td class="py-3">
                            <span class="d-block fw-bold text-dark mb-1 fs-6">{{ $slide->title ?? 'ไม่ได้ตั้งชื่อ Alt Text ประกอบรูปภาพ' }}</span>
                            @if($slide->link_url)
                                <a href="{{ $slide->link_url }}" target="_blank" class="small text-primary text-decoration-none d-inline-flex align-items-center gap-1">
                                     Link: <span class="text-truncate" style="max-width: 350px;">{{ $slide->link_url }}</span>
                                </a>
                            @else
                                <span class="small text-muted italic">ไม่มีการเชื่อมต่อลิงก์ปลายทาง</span>
                            @endif
                        </td>
                        <td class="text-center py-3">
                            @if($slide->is_active)
                                <span class="badge px-3 py-2 bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill fw-bold"><i class="bi bi-circle-fill text-success" style="font-size: 0.7rem;"></i> เปิดใช้งาน</span>
                            @else
                                <span class="badge px-3 py-2 bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill fw-bold"><i class="bi bi-circle-fill text-secondary" style="font-size: 0.7rem;"></i> ปิดการแสดงผล</span>
                            @endif
                        </td>
                        <td class="text-center py-3 pe-4">
                            <div class="d-inline-flex gap-2">
                                <button class="btn btn-sm btn-outline-warning fw-bold px-3 d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#editSlideModal-{{ $slide->id }}">
                                    <i class="bi bi-pencil-square"></i> แก้ไข
                                </button>
                                <form action="{{ route('admin.slideshows.destroy', $slide->id) }}" method="POST" class="d-inline form-delete-action">
                                    @csrf 
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger fw-bold px-3">
                                        <i class="bi bi-x-lg"></i> ลบ
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-5 bg-light-subtle">
                            <div class="py-4">
                                <span class="fs-1 d-block mb-2"><i class="bi bi-folder"></i></span>
                                <span class="fw-medium">ยังไม่มีข้อมูลแบนเนอร์สไลด์โชว์ขึ้นระบบไว้ในคลังกลาง</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!--  SAFE ZONE: โซนเรนเดอร์หน้าต่าง Modals ป้องกัน DOM ผิดผังการซ้อนทับ -->
<!-- ========================================================================= -->

<!--  1. ลูปแยกสำหรับแก้ไขรายละเอียดรายแบนเนอร์เดี่ยว -->
@foreach($slideshows as $slide)
<div class="modal fade" id="editSlideModal-{{ $slide->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.slideshows.update', $slide->id) }}" method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow-lg">
            @csrf 
            @method('PUT')
            <div class="modal-header bg-light border-bottom py-3 px-4">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-pencil-square"></i> แก้ไขข้อมูลแบนเนอร์สไลด์โชว์</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-4 text-center">
                    <label class="form-label small fw-bold text-secondary d-block text-start mb-2">️ ภาพแบนเนอร์ปัจจุบันที่แสดงผล</label>
                    <div class="d-inline-block p-1 bg-light border rounded mb-3 shadow-3xs" style="width: 100%; max-height: 150px; overflow: hidden;">
                        <img src="{{ asset('storage/' . $slide->image_path) }}" class="rounded img-fluid" style="width: 100%; height: 100%; object-fit: contain;">
                    </div>
                    <input type="file" name="image_path" class="form-control form-control-sm" accept="image/*">
                    <div class="form-text text-start text-muted mt-1">อัปโหลดภาพชุดใหม่เข้ามาเฉพาะเมื่อต้องการเปลี่ยนรูปเดิม (ความจุสูงสุด 4MB)</div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">️ หัวข้อคำอธิบายแบนเนอร์ (SEO Alt Text)</label>
                    <input type="text" name="title" class="form-control shadow-3xs" value="{{ $slide->title }}" placeholder="กรอกคำอธิบายรูปภาพสำหรับระบบค้นหา...">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark"> ลิงก์ปลายทางข้ามเครือข่าย (URL Destination)</label>
                    <input type="url" name="link_url" class="form-control shadow-3xs" value="{{ $slide->link_url }}" placeholder="https://example.com/page...">
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold text-dark"> ลำดับจัดเรียงภาพสไลด์</label>
                        <input type="number" name="sort_order" class="form-control shadow-3xs" value="{{ $slide->sort_order }}" min="0" required>
                    </div>
                    <div class="col-6 d-flex align-items-end pb-1">
                        <div class="form-check form-switch p-3 bg-light rounded border w-100 ms-0">
                            <input class="form-check-input ms-0 me-2" type="checkbox" name="is_active" id="activeEdit-{{ $slide->id }}" {{ $slide->is_active ? 'checked' : '' }}>
                            <label class="form-check-label small fw-bold text-success" for="activeEdit-{{ $slide->id }}">เปิดใช้งานบนเว็บ</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top py-3 px-4">
                <button type="button" class="btn btn-secondary fw-semibold px-4" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" class="btn btn-warning fw-bold text-dark px-4 shadow-sm"><i class="bi bi-floppy"></i> บันทึกการเปลี่ยนแปลง</button>
            </div>
        </form>
    </div>
</div>
@endforeach

<!--  2. ฟอร์มสร้างแบนเนอร์ใหม่สดตั้งต้น -->
<div class="modal fade" id="createSlideModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.slideshows.store') }}" method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow-lg">
            @csrf
            <div class="modal-header text-white border-bottom py-3 px-4" style="background-color: var(--theme-indigo);">
                <h5 class="modal-title fw-bold"><i class="bi bi-pin-angle"></i> เพิ่มแบนเนอร์สไลด์โชว์ชิ้นใหม่</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-danger"> เลือกไฟล์ภาพแบนเนอร์หลัก * (รองรับ JPG, PNG, WEBP)</label>
                    <input type="file" name="image_path" class="form-control shadow-3xs" accept="image/*" required>
                    <div class="form-text text-muted">ขนาดแนะนำ: สัดส่วน Landscape อัตราส่วน 16:9 หรือ 21:9 (ความจุสูงสุด 4MB)</div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">️ คำบรรยายภาพถ่ายโชว์ (SEO Alt Text สำหรับ Google Bot)</label>
                    <input type="text" name="title" class="form-control shadow-3xs" placeholder="เช่น กิจกรรมสืบสานประเพณี สำนักศิลปะฯ...">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark"> ลิงก์จุดเชื่อมต่อเมื่อผู้ใช้กดคลิกรูป (URL Destination)</label>
                    <input type="url" name="link_url" class="form-control shadow-3xs" placeholder="https://www.tru.ac.th/news/...">
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold text-dark"> กำหนดตำแหน่งคีย์ลำดับ</label>
                        <input type="number" name="sort_order" class="form-control shadow-3xs" placeholder="ปล่อยว่างเพื่อต่อท้ายอัตโนมัติ" min="0">
                    </div>
                    <div class="col-6 d-flex align-items-end pb-1">
                        <div class="form-check form-switch p-3 bg-light rounded border w-100 ms-0">
                            <input class="form-check-input ms-0 me-2" type="checkbox" name="is_active" id="activeNew" checked>
                            <label class="form-check-label small fw-bold text-success" for="activeNew">เปิดใช้งานทันที</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top py-3 px-4">
                <button type="button" class="btn btn-secondary fw-semibold px-4" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" class="btn btn-success fw-bold px-4 shadow-sm" style="background-color: #198754; border-color: #198754;"><i class="bi bi-floppy"></i> บันทึกข้อมูลเข้าดิสก์</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('admin_scripts')
<script>
    $(document).ready(function() {
        $('.form-delete-action').on('submit', function(e) {
            e.preventDefault();
            var processingForm = this;
            Swal.fire({
                title: ' ยืนยันทำลายแบนเนอร์ประชาสัมพันธ์?',
                text: "เรคคอร์ดข้อมูลและตัวไฟล์ภาพดิบใน Storage จะถูกกวาดล้างออกจากระบบเซิร์ฟเวอร์แบบถาวร!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#202040',
                confirmButtonText: ' ยืนยันคำสั่งลบถาวร',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) { 
                    processingForm.submit(); 
                }
            });
        });
    });
</script>
@endpush