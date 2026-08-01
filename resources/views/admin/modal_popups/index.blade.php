@extends('layouts.admin')

@section('title', 'จัดการป๊อปอัปแจ้งเตือนประกาศ')

@section('admin_content')
<div class="container-fluid animate__animated animate__fadeIn">
    <!-- ส่วนหัวหน้าจอแผงควบคุมหลัก -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="h4 mb-1 text-dark fw-bold"><i class="bi bi-megaphone"></i> ระบบบริหารจัดการหน้าต่างป๊อปอัปประกาศ (Modal Popup Center)</h2>
            <p class="text-muted small mb-0">โครงข่ายกำหนดสิทธิ์ป๊อปอัปหลักหน้าแรก ซึ่งผูกพันลอจิกคัดกรองช่วงเวลาหมดอายุออโต้ในระดับ Query Engine</p>
        </div>
        <button class="btn btn-danger shadow-sm fw-bold px-4 py-2" data-bs-toggle="modal" data-bs-target="#createPopupModal" style="background-color: #dc3545; border-color: #dc3545;">
            <i class="bi bi-sparkles"></i> สร้างหน้าต่างป๊อปอัปใหม่
        </button>
    </div>

    <!-- แผงมอนิเตอร์สถานะธุรกรรมข่าวสาร -->
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show" role="alert" style="border-left: 4px solid #198754 !important;">
            <span class="fw-medium"><i class="bi bi-floppy"></i> สำเร็จ:</span> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm" style="border-left: 4px solid #dc3545 !important;">
            <span class="fw-bold d-block mb-1">️ ตรวจพบการกรอกข้อมูลไม่ถูกต้อง:</span>
            <ul class="mb-0 ps-3 small">
                @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    @endif

    <!-- กระดานตารางรายการสารสนเทศป๊อปอัปหลัก (Responsive Data Table) -->
    <div class="card main-content-card border-0 shadow-sm overflow-hidden" style="border-top: 3px solid #dc3545 !important;">
        <div class="card-header bg-white border-0 py-3 px-4">
            <h5 class="m-0 fw-bold text-dark" style="color: #dc3545 !important;"><i class="bi bi-clipboard"></i> ประวัติคลังชุดป๊อปอัปสื่อสารองค์กร</h5>
        </div>
        <div class="table-responsive style-scrollbar">
            <table class="table table-hover align-middle mb-0 text-nowrap">
                <thead class="bg-light text-secondary fw-bold" style="font-size: 0.85rem;">
                    <tr>
                        <th width="140" class="text-center py-3 ps-4">รูปภาพสื่อสาร</th>
                        <th>ชื่อแคมเปญ / ปลายทางลิงก์เชื่อมโยง</th>
                        <th width="260" class="text-center py-3">⏳ หน้าต่างเงื่อนไขเวลาแสดงผลสากล</th>
                        <th width="140" class="text-center py-3">สถานะ Flag</th>
                        <th width="140" class="text-center py-3 pe-4">จัดการระบบ</th>
                    </tr>
                </thead>
                <tbody class="border-top-0" style="font-size: 0.9rem;">
                    @forelse($popups as $popup)
                    <tr>
                        <td class="text-center py-3 ps-4">
                            <div class="d-inline-block rounded border p-1 bg-white shadow-3xs" style="width: 90px; height: 90px; overflow: hidden;">
                                <img src="{{ asset('storage/' . $popup->image_path) }}" class="w-100 h-100 rounded" style="object-fit: cover; object-position: center;">
                            </div>
                        </td>
                        <td class="py-3">
                            <strong class="d-block text-dark mb-1 fs-6">{{ $popup->title }}</strong>
                            <span class="small text-muted d-block">
                                @if($popup->link_url)
                                    <a href="{{ $popup->link_url }}" target="_blank" class="text-primary text-decoration-none d-inline-flex align-items-center gap-1">
                                         แนบ URL: <span class="text-truncate" style="max-width: 250px;">{{ $popup->link_url }}</span>
                                    </a>
                                @else
                                    <span class="text-secondary italic">เป็นรูปภาพนิ่ง ไม่มีลิงก์พ่วงท้าย</span>
                                @endif
                            </span>
                        </td>
                        <td class="text-center py-3 small bg-light-subtle">
                            <div class="d-flex flex-column gap-1 align-items-center">
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-10 px-2 py-1 rounded w-100 max-w-200">
                                     เริ่ม: {{ $popup->start_date ? $popup->start_date->format('d/m/Y H:i') : 'ปล่อยทำงานทันที' }}
                                </span>
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-10 px-2 py-1 rounded w-100 max-w-200">
                                     สิ้นสุด: {{ $popup->end_date ? $popup->end_date->format('d/m/Y H:i') : 'เปิดยาว ไม่มีวันหมดอายุ' }}
                                </span>
                            </div>
                        </td>
                        <td class="text-center py-3">
                            @if($popup->is_active)
                                <span class="badge px-3 py-2 bg-success text-white shadow-3xs rounded-pill fw-bold" style="background: linear-gradient(135deg, #198754, #146c43) !important;"><i class="bi bi-crown"></i> หลักหน้าแรก</span>
                            @else
                                <span class="badge px-3 py-2 bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill fw-bold">ปิดสำรองไว้</span>
                            @endif
                        </td>
                        <td class="text-center py-3 pe-4">
                            <div class="d-inline-flex gap-2">
                                <button class="btn btn-sm btn-outline-warning fw-bold px-3" data-bs-toggle="modal" data-bs-target="#editPopupModal-{{ $popup->id }}"><i class="bi bi-pencil-square"></i> แก้ไข</button>
                                <form action="{{ route('admin.modal-popups.destroy', $popup->id) }}" method="POST" class="d-inline form-delete-action">
                                    @csrf 
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger fw-bold px-3"><i class="bi bi-x-lg"></i> ลบ</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-5 bg-light-subtle">
                            <div class="py-4">
                                <span class="fs-1 d-block mb-2"><i class="bi bi-megaphone"></i></span>
                                <span class="fw-medium">ยังไม่มีข้อมูลหน้าต่างป๊อปอัปแจ้งเตือนประกาศเผยแพร่อยู่ในระบบคลัง</span>
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
<!--  SAFE ZONE: พื้นที่สถิตหน้าต่างเครื่องมือลับหลังบ้าน (อยู่นอกโครงสร้างตารางเด็ดขาด) -->
<!-- ========================================================================= -->

<!--  1. ลูปแยกสำหรับสร้างหน้าต่างอัปเดตข้อมูล (Edit Modals Lifecycle Container) -->
@foreach($popups as $popup)
<div class="modal fade" id="editPopupModal-{{ $popup->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.modal-popups.update', $popup->id) }}" method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow-lg">
            @csrf 
            @method('PUT')
            <div class="modal-header bg-light border-bottom py-3 px-4">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-pencil-square"></i> ปรับแต่งรายละเอียดหน้าต่างป๊อปอัป</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-4 text-center">
                    <label class="form-label small fw-bold text-secondary d-block text-start mb-2"><i class="bi bi-images"></i> สื่อโฆษณาที่แสดงผลปัจจุบัน</label>
                    <div class="d-inline-block p-1 bg-light border rounded mb-3 shadow-3xs" style="width: 150px; height: 150px; overflow: hidden;">
                        <img src="{{ asset('storage/' . $popup->image_path) }}" class="rounded w-100 h-100" style="object-fit: cover;">
                    </div>
                    <input type="file" name="image_path" class="form-control form-control-sm" accept="image/*">
                    <div class="form-text text-muted text-start mt-1">อัปโหลดรูปภาพใหม่ทดแทนสัดส่วนเดิมเมื่อจำเป็นเท่านั้น (จำกัดไฟล์ 4MB)</div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">️ ชื่อหัวข้อประกาศโครงการ / แคมเปญหลัก *</label>
                    <input type="text" name="title" class="form-control shadow-3xs" value="{{ $popup->title }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark"> ฝังลิงก์ปลายทางภายนอกเมื่อกดคลิกภาพ (URL Target)</label>
                    <input type="url" name="link_url" class="form-control shadow-3xs" value="{{ $popup->link_url }}" placeholder="https://...">
                </div>
                <div class="row g-3 bg-light p-3 rounded border mt-3 mb-1">
                    <div class="col-6">
                        <label class="form-label small fw-bold text-success">⏳ ตั้งเวลาเริ่มแสดงผล</label>
                        <input type="datetime-local" name="start_date" class="form-control form-control-sm shadow-3xs" value="{{ $popup->start_date ? $popup->start_date->format('Y-m-d\TH:i') : '' }}">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold text-danger">⏳ เวลาหมดอายุซ่อนไฟล์</label>
                        <input type="datetime-local" name="end_date" class="form-control form-control-sm shadow-3xs" value="{{ $popup->end_date ? $popup->end_date->format('Y-m-d\TH:i') : '' }}">
                    </div>
                    <div class="col-12 mt-3">
                        <div class="form-check form-switch p-2 bg-white rounded border w-100 ms-0 ps-5">
                            <input class="form-check-input ms-0 me-2" type="checkbox" name="is_active" id="activePopupEdit-{{ $popup->id }}" {{ $popup->is_active ? 'checked' : '' }} style="margin-left: -2.5rem !important;">
                            <label class="form-check-label small fw-bold text-success" for="activePopupEdit-{{ $popup->id }}">เปิดเป็นป๊อปอัปหลัก (ระบบจะตัดสิทธิ์ตัวอื่นออกออโต้)</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top py-3 px-4">
                <button type="button" class="btn btn-secondary fw-semibold px-4" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" class="btn btn-primary fw-bold px-4 shadow-sm" style="background-color: var(--theme-indigo); border-color: var(--theme-indigo);"><i class="bi bi-floppy"></i> ยืนยันบันทึกข้อมูล</button>
            </div>
        </form>
    </div>
</div>
@endforeach

<!--  2. แท่นยิงสร้างประกาศป๊อปอัปชิ้นใหม่แกะกล่อง (Create Model Area) -->
<div class="modal fade" id="createPopupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.modal-popups.store') }}" method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow-lg">
            @csrf
            <div class="modal-header text-white border-bottom py-3 px-4" style="background-color: #dc3545;">
                <h5 class="modal-title fw-bold"><i class="bi bi-pin-angle"></i> จัดสร้างกรอบหน้าต่างป๊อปอัปประกาศใหม่</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-danger"> เลือกอัปโหลดภาพโฆษณาประชาสัมพันธ์หลัก *</label>
                    <input type="file" name="image_path" class="form-control shadow-3xs" accept="image/*" required>
                    <div class="form-text text-muted">ขนาดแนะนำ: อัตราส่วนสี่เหลี่ยมจัตุรัส 1:1 (เช่น 800x800px) เพื่อสัดส่วนแสงป๊อปอัปที่สวยงาม</div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">️ ชื่ออ้างอิงแคมเปญ / โครงการสารสนเทศองค์กร *</label>
                    <input type="text" name="title" class="form-control shadow-3xs" placeholder="เช่น ประกาศปิดระบบปรับปรุงเซิร์ฟเวอร์ชั่วคราว..." required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark"> ลิงก์เชื่อมโยงสถิตภายนอกเมื่อแอดมินกดเปิดดู (URL Target)</label>
                    <input type="url" name="link_url" class="form-control shadow-3xs" placeholder="https://example.com/target-page">
                </div>
                <div class="row g-3 bg-light p-3 rounded border mt-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold text-success">⏳ เวลาเปิดโชว์อัตโนมัติ (ปล่อยว่าง=สดทันที)</label>
                        <input type="datetime-local" name="start_date" class="form-control form-control-sm shadow-3xs">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold text-danger">⏳ เวลาหมดอายุซ่อนตัว (ปล่อยว่าง=เปิดค้างยาว)</label>
                        <input type="datetime-local" name="end_date" class="form-control form-control-sm shadow-3xs">
                    </div>
                    <div class="col-12 mt-3">
                        <div class="form-check form-switch p-2 bg-white rounded border w-100 ms-0 ps-5">
                            <input class="form-check-input ms-0 me-2" type="checkbox" name="is_active" id="activePopupNew" style="margin-left: -2.5rem !important;">
                            <label class="form-check-label small fw-bold text-success" for="activePopupNew">ตั้งเป็นป๊อปอัปหลักหน้าร้านทันที</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top py-3 px-4">
                <button type="button" class="btn btn-secondary fw-semibold px-4" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" class="btn btn-danger fw-bold px-4 shadow-sm" style="background-color: #dc3545; border-color: #dc3545;"><i class="bi bi-floppy"></i> บันทึกและติดตั้งป๊อปอัป</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('admin_scripts')
<script>
    $(document).ready(function() {
        // จัดการดักระบายคำสั่งลบข้อมูลป๊อปอัปผ่าน SweetAlert2 ป้องกันปุ่มเบิ้ล
        $('.form-delete-action').on('submit', function(e) {
            e.preventDefault();
            var handlingForm = this;
            Swal.fire({
                title: ' ยืนยันทำลายป๊อปอัปประกาศชิ้นนี้?',
                text: "ข้อมูลพร้อมไฟล์ภาพโฆษณาในคลังระบบจะถูกลบออกแบบถาวรจากดิสก์ทันที!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#202040',
                confirmButtonText: ' ยืนยันทำลายถาวร',
                cancelButtonText: 'ยกเลิกคำสั่ง'
            }).then((result) => {
                if (result.isConfirmed) { 
                    handlingForm.submit(); 
                }
            });
        });
    });
</script>
@endpush