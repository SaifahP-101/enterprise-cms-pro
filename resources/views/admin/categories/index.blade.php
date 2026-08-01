@extends('layouts.admin')

@section('title', 'จัดการหมวดหมู่เนื้อหา')

@section('admin_content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-1 text-dark fw-bold"><i class="bi bi-folder2-open"></i> ระบบจัดการหมวดหมู่เนื้อหา (Content Categories)</h2>
            <p class="text-muted small mb-0">แยกประเภทกลุ่มข้อมูลมรดกทางวัฒนธรรมและข่าวสาร เพื่อความเป็นระเบียบในการจัดทำดัชนี</p>
        </div>
        <button class="btn btn-sm btn-success px-4 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
            <i class="bi bi-sparkles"></i> เพิ่มหมวดหมู่ใหม่
        </button>
    </div>

    <div class="card main-content-card shadow-sm border-0">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table id="categoriesTable" class="table table-striped align-middle" style="width:100%">
                    <thead class="table-light text-secondary small text-uppercase">
                        <tr>
                            <th style="width: 80px;">ลำดับเรียง</th>
                            <th>ชื่อหมวดหมู่สารสนเทศ</th>
                            <th>ข้อกำหนดรูปภาพ (Size & Type)</th>
                            <th>ระบบ URL Slug (SEO)</th>
                            <th>สถานะการใช้งาน</th>
                            <th class="text-end" style="width: 160px;">การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $cat)
                            <tr>
                                <td class="fw-bold text-center text-primary">{{ $cat->sort_order }}</td>
                                <td><span class="fw-semibold text-dark">{{ $cat->name }}</span></td>
                                <td>
                                    <span class="badge bg-light text-dark border fw-medium px-2 py-1">
                                        <i class="bi bi-aspect-ratio text-primary me-1"></i>{{ $cat->image_dimension ?? '1200 x 630 Pixels' }}
                                        <span class="mx-1 text-muted">|</span>
                                        <i class="bi bi-hdd text-success me-1"></i>{{ $cat->image_size ?? '2MB' }}
                                        <span class="mx-1 text-muted">|</span>
                                        <i class="bi bi-filetype-png text-danger me-1"></i>{{ $cat->image_type ?? '.JPG .png' }}
                                    </span>
                                </td>
                                <td><code class="text-muted">/category/{{ $cat->slug }}</code></td>
                                <td>
                                    @if($cat->is_active)
                                        <span class="badge bg-success px-2 py-1 rounded-1">เปิดใช้งาน</span>
                                    @else
                                        <span class="badge bg-secondary px-2 py-1 rounded-1">ปิดระบบ</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-xs btn-outline-primary me-1 px-2 py-1" data-bs-toggle="modal" data-bs-target="#editCategoryModal{{ $cat->id }}">แก้ไข</button>
                                    <form action="{{ route('admin.categories.destroy', $cat->id) }}" method="POST" class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-xs btn-outline-danger px-2 py-1 btn-delete">ลบ</button>
                                    </form>
                                </td>
                            </tr>

                            <!-- Modal สำหรับแก้ไขหมวดหมู่ย่อย -->
                            <div class="modal fade" id="editCategoryModal{{ $cat->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <form action="{{ route('admin.categories.update', $cat->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-content border-top border-4 border-primary">
                                            <div class="modal-header bg-light">
                                                <h5 class="modal-title fw-bold text-dark fs-6"><i class="bi bi-pencil-square"></i> แก้ไขข้อมูลหมวดหมู่</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body shadow-xs">
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">ชื่อหมวดหมู่</label>
                                                    <input type="text" name="name" class="form-control" value="{{ $cat->name }}" required>
                                                </div>
                                                <div class="row g-2 mb-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold">ขนาดภาพแนะนำ (Dimension)</label>
                                                        <input type="text" name="image_dimension" class="form-control form-control-sm" value="{{ $cat->image_dimension ?? '1200 x 630 Pixels' }}" placeholder="เช่น 1200 x 630 Pixels">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold">ขนาดไฟล์สูงสุด (Size)</label>
                                                        <input type="text" name="image_size" class="form-control form-control-sm" value="{{ $cat->image_size ?? '2MB' }}" placeholder="เช่น 2MB">
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">ชนิดไฟล์ที่รองรับ (Allowed Types)</label>
                                                    <input type="text" name="image_type" class="form-control form-control-sm" value="{{ $cat->image_type ?? '.JPG .png' }}" placeholder="เช่น .JPG .png">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">ลำดับการจัดเรียง (Sort Order)</label>
                                                    <input type="number" name="sort_order" class="form-control" value="{{ $cat->sort_order }}" min="0" required>
                                                </div>
                                                <div class="form-check form-switch mt-2">
                                                    <input type="checkbox" name="is_active" class="form-check-input" id="editSwitch{{ $cat->id }}" {{ $cat->is_active ? 'checked' : '' }}>
                                                    <label class="form-check-label small fw-bold" for="editSwitch{{ $cat->id }}">เปิดแสดงผลบนเว็บไซต์</label>
                                                </div>
                                            </div>
                                            <div class="modal-footer bg-light py-2">
                                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">ยกเลิก</button>
                                                <button type="submit" class="btn btn-primary btn-sm px-4">บันทึกแก้ไข</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal สำหรับเพิ่มหมวดหมู่ใหม่ -->
<div class="modal fade" id="createCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.categories.store') }}" method="POST">
            @csrf
            <div class="modal-content border-top border-4 border-success">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-dark fs-6"><i class="bi bi-sparkles"></i> เพิ่มหมวดหมู่ข้อมูลศิลปวัฒนธรรม</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">ชื่อหมวดหมู่ที่ต้องการสร้าง</label>
                        <input type="text" name="name" class="form-control" placeholder="เช่น ภูมิปัญญาท้องถิ่น, ข่าวจัดซื้อจัดจ้าง" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">ขนาดภาพแนะนำ (Dimension)</label>
                            <input type="text" name="image_dimension" class="form-control form-control-sm" placeholder="1200 x 630 Pixels" value="1200 x 630 Pixels">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">ขนาดไฟล์สูงสุด (Size)</label>
                            <input type="text" name="image_size" class="form-control form-control-sm" placeholder="2MB" value="2MB">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">ชนิดไฟล์ที่รองรับ (Allowed Types)</label>
                        <input type="text" name="image_type" class="form-control form-control-sm" placeholder=".JPG .png" value=".JPG .png">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">ลำดับการคัดกรองจัดเรียง</label>
                        <input type="number" name="sort_order" class="form-control" value="0" min="0" required>
                    </div>
                    <div class="form-check form-switch mt-2">
                        <input type="checkbox" name="is_active" class="form-check-input" id="createSwitch" checked>
                        <label class="form-check-label small fw-bold" for="createSwitch">เปิดการแสดงผลทันที</label>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">ปิด</button>
                    <button type="submit" class="btn btn-success btn-sm px-4">ยืนยันสร้าง</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('admin_scripts')
<script>
    $(document).ready(function() {
        //  ติดตั้งระบบสืบค้นข้อมูล DataTables
        $('#categoriesTable').DataTable({
            responsive: true,
            order: [[0, "asc"]],
            language: { search: " ค้นหาหมวดหมู่ด่วน:", lengthMenu: "แสดง _MENU_ แถว", info: "แสดงรายการ _START_ ถึง _END_ จากทั้งหมด _TOTAL_ หมวดหมู่" }
        });

        //  ระบบยืนยันการลบผ่าน SweetAlert2 คุมธีมสีทองคำองค์กร (#DAA520)
        $('.btn-delete').click(function(e) {
            e.preventDefault();
            var form = $(this).closest('form');
            Swal.fire({
                title: 'ยืนยันการลบหมวดหมู่?',
                text: "เตือน! บทความและกิจกรรมทั้งหมดที่ผูกโยงอยู่กับหมวดหมู่นี้จะถูกนำออกจากสารบบเว็บไซต์ด้วย!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#DAA520',
                cancelButtonColor: '#202040',
                confirmButtonText: ' ยืนยันลบทิ้ง',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => { if (result.isConfirmed) form.submit(); });
        });
    });
</script>
@endpush