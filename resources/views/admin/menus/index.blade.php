@extends('layouts.admin')

@section('title', 'จัดการเมนูอัจฉริยะ')

@section('admin_content')
<div class="container-fluid animate__animated animate__fadeIn">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="h4 mb-1 text-dark fw-bold"><i class="bi bi-list-nested"></i> ระบบจัดการผังโครงสร้างเมนูไดนามิก (Navigation Tree Vault)</h2>
            <p class="text-muted small mb-0">โครงข่ายจัดสรรลิงก์เส้นทางหน้าเว็บ รองรับความสัมพันธ์แบบผูกกิ่งย่อยอ้างอิงตัวเอง (Self-Referencing)</p>
        </div>
        <button class="btn btn-primary shadow-sm fw-bold px-4 py-2" data-bs-toggle="modal" data-bs-target="#createMenuModal" style="background-color: var(--theme-indigo); border-color: var(--theme-indigo);">
            <i class="bi bi-sparkles"></i> สร้างแถบเมนูใหม่
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show" role="alert" style="border-left: 4px solid #198754 !important;">
            <span class="fw-medium"><i class="bi bi-floppy"></i> สำเร็จ:</span> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- กระดานผังแสดงรายการเมนู -->
    <div class="card main-content-card border-0 shadow-sm mb-5">
        <div class="card-header bg-white border-0 py-3 px-4">
            <h5 class="m-0 fw-bold text-dark" style="color: var(--theme-indigo) !important;"><i class="bi bi-clipboard"></i> ผังโครงสร้างกิ่งเมนูทั้งหมดในระบบคลังกลาง</h5>
        </div>
        <div class="card-body p-4 bg-light bg-opacity-50">
            <div class="row row-cols-1 row-cols-md-2 g-4">
                @forelse($menus as $menu)
                    <div class="col">
                        <div class="p-3 bg-white border rounded shadow-3xs h-100">
                            <!-- แถบเมนูแม่หลัก (Parent Header) -->
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2 bg-light p-2 rounded">
                                <div>
                                    <span class="badge bg-dark me-2">ลำดับ {{ $menu->sort_order }}</span>
                                    <strong class="text-dark fs-6"><i class="bi bi-pin-angle"></i> {{ $menu->title }}</strong>
                                    <small class="text-muted d-block ps-4">Path: `{{ $menu->url ?? '/' }}`</small>
                                </div>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-xs btn-outline-warning p-1 px-2 text-dark font-weight-bold" data-bs-toggle="modal" data-bs-target="#editMenuModal-{{ $menu->id }}" style="font-size:0.75rem;"><i class="bi bi-pencil-square"></i> แก้ไข</button>
                                    <form action="{{ route('admin.menus.destroy', $menu->id) }}" method="POST" class="d-inline form-delete-gate">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-outline-danger p-1 px-2 font-weight-bold" style="font-size:0.75rem;"><i class="bi bi-x-lg"></i> ลบ</button>
                                    </form>
                                </div>
                            </div>

                            <!-- แถบลิสต์เมนูลูกกิ่งย่อยชั้นใน (Children Node List) -->
                            <div class="ps-3 mt-2">
                                @forelse($menu->children as $child)
                                    <div class="d-flex justify-content-between align-items-center p-2 border-bottom border-dashed small hover-bg-light rounded">
                                        <div class="text-secondary">
                                            <span class="text-muted">┗ ลำดับ {{ $child->sort_order }}:</span> 
                                            <span class="fw-bold text-dark">{{ $child->title }}</span>
                                            <code class="ms-2 text-primary d-inline-block text-truncate" style="font-size: 0.75rem; max-width: 150px; vertical-align: bottom;">
    ({{ $child->url ?? '#' }})
</code>
                                        </div>
                                        <div class="d-flex gap-1">
                                            <button class="btn btn-xs text-warning p-0 border-0 bg-transparent fw-bold" data-bs-toggle="modal" data-bs-target="#editMenuModal-{{ $child->id }}" style="font-size:0.75rem;"><i class="bi bi-gear"></i></button>
                                            <form action="{{ route('admin.menus.destroy', $child->id) }}" method="POST" class="d-inline form-delete-gate">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-xs text-danger p-0 border-0 bg-transparent fw-bold" style="font-size:0.75rem;"><i class="bi bi-trash3"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-muted italic small py-2 ps-2"> เมนูหลักรายการนี้ยังไม่มีการแตกกิ่งเมนูย่อย</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5 text-muted small bg-white border rounded">ยังไม่มีชุดข้อมูลโครงสร้างเมนูจัดตั้งไว้ในระบบคลังกลาง</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!--  SAFE WORKSPACE LAYER: พื้นที่สถิตหน้าต่างควบคุมป้องกันโครงสร้างตารางรวน -->
<!-- ========================================================================= -->

<!--  1. หน้าต่างจัดสร้างเมนูใหม่แกะกล่อง (Create Menu Modal) -->
<div class="modal fade" id="createMenuModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.menus.store') }}" method="POST" class="modal-content border-0 shadow-lg">
            @csrf
            <div class="modal-header text-white border-bottom py-3 px-4" style="background-color: var(--theme-indigo);">
                <h5 class="modal-title fw-bold"><i class="bi bi-pin-angle"></i> เพิ่มโครงสร้างแถบเมนูใหม่</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark"><i class="bi bi-pencil-square"></i> ชื่อข้อความเมนูแสดงผล *</label>
                    <input type="text" name="title" class="form-control" placeholder="เช่น ประวัติสำนักฯ, บริการวิชาการ..." required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark"> เส้นทางปลายทาง (URL หรือ Route Path Path)</label>
                    <input type="text" name="url" class="form-control" placeholder="เช่น /about-us หรือ https://...">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary"><i class="bi bi-building-columns"></i> สังกัดภายใต้เมนูแม่หลัก (กรณีทำเมนูย่อยแตกกิ่ง)</label>
                    <select name="parent_id" class="form-select">
                        <option value="">-- ตั้งเป็นเมนูหลักสูงสุด (Root Level) --</option>
                        @foreach($parentMenus as $pm)
                            <option value="{{ $pm->id }}"><i class="bi bi-pin-angle"></i> ผูกเป็นกิ่งย่อยของ: {{ $pm->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold text-dark"> กำหนดคีย์ลำดับเรียง</label>
                        <input type="number" name="sort_order" class="form-control" placeholder="ปล่อยว่างเพื่อรันต่อท้าย" min="0">
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
                <button type="button" class="btn btn-secondary fw-semibold" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" class="btn btn-success fw-bold px-4 shadow-sm" style="background-color: #198754; border-color: #198754;"><i class="bi bi-floppy"></i> บันทึกติดตั้งเมนู</button>
            </div>
        </form>
    </div>
</div>

<!--  2. ลูปสร้างหน้าต่างปรับปรุงแก้ไขสำหรับเมนูทุกตัว (รวมทั้งแม่และลูก) -->
@foreach($menus as $menu)
    @include('admin.menus.partials.edit_modal', ['item' => $menu, 'parentMenus' => $parentMenus])
    @foreach($menu->children as $child)
        @include('admin.menus.partials.edit_modal', ['item' => $child, 'parentMenus' => $parentMenus])
    @endforeach
@endforeach

@endsection

@push('admin_scripts')
<script>
    $(document).ready(function() {
        // ผูกกลไกป้องกันคำสั่งลบเบิ้ลด้วยขุมพลัง SweetAlert2
        $('.form-delete-gate').on('submit', function(e) {
            e.preventDefault();
            var targetForm = this;
            Swal.fire({
                title: ' ยืนยันถอดถอนรายการเมนู?',
                text: "หากลบเมนูแม่หลัก กิ่งเมนูย่อยชั้นในทั้งหมดที่ผูกพันกันอยู่จะถูกทลายทำลายทิ้งถาวร!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#202040',
                confirmButtonText: ' ยืนยันสั่งลบถาวร',
                cancelButtonText: 'รักษาไฟล์ไว้'
            }).then((result) => {
                if (result.isConfirmed) { targetForm.submit(); }
            });
        });
    });
</script>
@endpush