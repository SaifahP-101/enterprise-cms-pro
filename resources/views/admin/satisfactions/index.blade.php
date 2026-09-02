@extends('layouts.admin')

@section('title', 'จัดการข้อมูลสรุปความพึงพอใจ')

@section('styles')
<style>
    /* Custom Styling สำหรับหน้านี้ */
    .rating-badge {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--theme-indigo);
    }
    .progress-sm {
        height: 6px;
        border-radius: 4px;
        background-color: #E2E8F0;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(218, 165, 32, 0.05);
    }
</style>
@endsection

@section('admin_content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--theme-indigo);">
                <i class="bi bi-bar-chart-fill text-warning me-2"></i>จัดการข้อมูลสรุปความพึงพอใจ
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small text-muted">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">หน้าหลัก</a></li>
                    <li class="breadcrumb-item active" aria-current="page">สรุปความพึงพอใจ</li>
                </ol>
            </nav>
        </div>
        <div>
            <!-- ปุ่มเปิด Modal สร้างข้อมูลใหม่ -->
            <button type="button" class="btn btn-primary px-4 fw-semibold" style="background-color: var(--theme-indigo); border: none;" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="bi bi-plus-circle me-1"></i> เพิ่มข้อมูลสรุปใหม่
            </button>
        </div>
    </div>

    <!-- แจ้งเตือน Validation Errors ฝั่ง Frontend -->
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm" role="alert">
            <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i>เกิดข้อผิดพลาดในการบันทึกข้อมูล:</div>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Data Table Card -->
    <div class="card main-content-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background-color: #F8F9FA;">
                        <tr>
                            <th class="px-4 py-3 text-muted small text-uppercase">รอบการประเมิน (Period)</th>
                            <th class="px-4 py-3 text-muted small text-uppercase text-center">คะแนนรวม</th>
                            <th class="px-4 py-3 text-muted small text-uppercase text-center">มิติการประเมิน</th>
                            <th class="px-4 py-3 text-muted small text-uppercase text-center">สถานะหน้าเว็บ</th>
                            <th class="px-4 py-3 text-muted small text-uppercase text-end">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($summaries as $summary)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="fw-bold text-dark">{{ $summary->period }}</div>
                                    <div class="small text-muted"><i class="bi bi-people-fill me-1"></i> ผู้ตอบ: {{ number_format($summary->total_respondents) }} คน</div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="rating-badge">{{ number_format($summary->overall_rating, 2) }}</div>
                                    <div class="text-warning small">
                                        @for($i=1; $i<=5; $i++)
                                            <i class="bi bi-star{{ $i <= round($summary->overall_rating) ? '-fill' : '' }}"></i>
                                        @endfor
                                    </div>
                                </td>
                                <td class="px-4 py-3" style="min-width: 200px;">
                                    <!-- Progress Bars ขนาดย่อมิติการประเมิน -->
                                    <div class="d-flex justify-content-between small text-muted mb-1" style="font-size: 0.7rem;">
                                        <span>บริการ ({{ $summary->dimension_service }}%)</span>
                                    </div>
                                    <div class="progress progress-sm mb-2">
                                        <div class="progress-bar bg-primary" style="width: {{ $summary->dimension_service }}%"></div>
                                    </div>

                                    <div class="d-flex justify-content-between small text-muted mb-1" style="font-size: 0.7rem;">
                                        <span>บุคลากร ({{ $summary->dimension_staff }}%)</span>
                                    </div>
                                    <div class="progress progress-sm mb-2">
                                        <div class="progress-bar bg-success" style="width: {{ $summary->dimension_staff }}%"></div>
                                    </div>

                                    <div class="d-flex justify-content-between small text-muted mb-1" style="font-size: 0.7rem;">
                                        <span>สถานที่ ({{ $summary->dimension_facility }}%)</span>
                                    </div>
                                    <div class="progress progress-sm">
                                        <div class="progress-bar bg-warning" style="width: {{ $summary->dimension_facility }}%"></div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <!-- Toggle Publish Status -->
                                    <form action="{{ route('admin.satisfactions.toggle', $summary->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        @if($summary->is_published)
                                            <button type="submit" class="btn btn-sm rounded-pill px-3 fw-semibold text-success" style="background-color: #D1E7DD; border: none;">
                                                <i class="bi bi-check-circle-fill me-1"></i> เผยแพร่
                                            </button>
                                        @else
                                            <button type="submit" class="btn btn-sm rounded-pill px-3 fw-semibold text-secondary" style="background-color: #E2E8F0; border: none;">
                                                <i class="bi bi-eye-slash-fill me-1"></i> ซ่อน
                                            </button>
                                        @endif
                                    </form>
                                </td>
                                <td class="px-4 py-3 text-end">
                                    <!-- Edit Button -->
                                    <button class="btn btn-sm btn-outline-primary shadow-sm rounded-3 me-1" data-bs-toggle="modal" data-bs-target="#editModal{{ $summary->id }}">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    
                                    <!-- Delete Button (SweetAlert) -->
                                    <button class="btn btn-sm btn-outline-danger shadow-sm rounded-3" onclick="confirmDelete({{ $summary->id }})">
                                        <i class="bi bi-trash"></i>
                                    </button>

                                    <!-- Hidden Delete Form -->
                                    <form id="delete-form-{{ $summary->id }}" action="{{ route('admin.satisfactions.destroy', $summary->id) }}" method="POST" class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>

                            <!-- ⚡ MODAL: แก้ไขข้อมูล (อยู่ใน Loop) ⚡ -->
                            <div class="modal fade" id="editModal{{ $summary->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <div class="modal-content border-0 rounded-4 shadow">
                                        <div class="modal-header border-bottom-0 bg-light rounded-top-4">
                                            <h5 class="modal-title fw-bold text-dark"><i class="bi bi-pencil-square text-primary me-2"></i>แก้ไขข้อมูลความพึงพอใจ</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('admin.satisfactions.update', $summary->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body p-4">
                                                <div class="row g-3">
                                                    <div class="col-md-12">
                                                        <label class="form-label fw-semibold small">รอบการประเมิน (Period) <span class="text-danger">*</span></label>
                                                        <input type="text" name="period" class="form-control" value="{{ $summary->period }}" required placeholder="เช่น ประจำปีงบประมาณ 2567">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-semibold small">คะแนนเฉลี่ยรวม (เต็ม 5) <span class="text-danger">*</span></label>
                                                        <input type="number" step="0.01" min="0" max="5" name="overall_rating" class="form-control" value="{{ $summary->overall_rating }}" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-semibold small">จำนวนผู้ตอบแบบสอบถาม <span class="text-danger">*</span></label>
                                                        <input type="number" name="total_respondents" class="form-control" value="{{ $summary->total_respondents }}" required>
                                                    </div>
                                                    <div class="col-12"><hr class="opacity-10 my-1"></div>
                                                    <div class="col-md-4">
                                                        <label class="form-label fw-semibold small text-primary">ด้านบริการ (%) <span class="text-danger">*</span></label>
                                                        <input type="number" min="0" max="100" name="dimension_service" class="form-control border-primary" value="{{ $summary->dimension_service }}" required>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label fw-semibold small text-success">ด้านบุคลากร (%) <span class="text-danger">*</span></label>
                                                        <input type="number" min="0" max="100" name="dimension_staff" class="form-control border-success" value="{{ $summary->dimension_staff }}" required>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label fw-semibold small text-warning">ด้านสถานที่ (%) <span class="text-danger">*</span></label>
                                                        <input type="number" min="0" max="100" name="dimension_facility" class="form-control border-warning" value="{{ $summary->dimension_facility }}" required>
                                                    </div>
                                                    <div class="col-12 mt-4">
                                                        <div class="form-check form-switch form-check-inline">
                                                            <input class="form-check-input" type="checkbox" name="is_published" id="is_published_edit{{ $summary->id }}" value="1" {{ $summary->is_published ? 'checked' : '' }}>
                                                            <label class="form-check-label fw-semibold" for="is_published_edit{{ $summary->id }}">แสดงผลหน้าเว็บไซต์ทันที</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-top-0 bg-light rounded-bottom-4">
                                                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">ยกเลิก</button>
                                                <button type="submit" class="btn btn-primary px-4 fw-semibold" style="background-color: var(--theme-indigo);">บันทึกการแก้ไข</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-inboxes fs-1 d-block mb-2 opacity-50"></i>
                                    ยังไม่มีข้อมูลสรุปความพึงพอใจในระบบ
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4 mb-3">
                {{ $summaries->links() }}
            </div>
        </div>
    </div>
</div>

<!-- ⚡ MODAL: เพิ่มข้อมูลใหม่ ⚡ -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-bottom-0 bg-light rounded-top-4">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-plus-circle text-primary me-2"></i>เพิ่มข้อมูลสรุปความพึงพอใจ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.satisfactions.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold small">รอบการประเมิน (Period) <span class="text-danger">*</span></label>
                            <input type="text" name="period" class="form-control" value="{{ old('period') }}" required placeholder="เช่น ประจำปีงบประมาณ 2567">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">คะแนนเฉลี่ยรวม (เต็ม 5) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" max="5" name="overall_rating" class="form-control" value="{{ old('overall_rating') }}" required placeholder="เช่น 4.85">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">จำนวนผู้ตอบแบบสอบถาม <span class="text-danger">*</span></label>
                            <input type="number" name="total_respondents" class="form-control" value="{{ old('total_respondents') }}" required placeholder="เช่น 1250">
                        </div>
                        
                        <div class="col-12"><hr class="opacity-10 my-1"></div>
                        <div class="col-12">
                            <p class="mb-2 fw-semibold text-muted small">สัดส่วนความพึงพอใจรายด้าน (0 - 100%)</p>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-primary">ด้านบริการ (%) <span class="text-danger">*</span></label>
                            <input type="number" min="0" max="100" name="dimension_service" class="form-control border-primary" value="{{ old('dimension_service') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-success">ด้านบุคลากร (%) <span class="text-danger">*</span></label>
                            <input type="number" min="0" max="100" name="dimension_staff" class="form-control border-success" value="{{ old('dimension_staff') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-warning">ด้านสถานที่ (%) <span class="text-danger">*</span></label>
                            <input type="number" min="0" max="100" name="dimension_facility" class="form-control border-warning" value="{{ old('dimension_facility') }}" required>
                        </div>
                        
                        <div class="col-12 mt-4">
                            <div class="form-check form-switch form-check-inline">
                                <input class="form-check-input" type="checkbox" name="is_published" id="is_published_create" value="1" {{ old('is_published') ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="is_published_create">แสดงผลหน้าเว็บไซต์ทันที</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold" style="background-color: var(--theme-indigo);">บันทึกข้อมูล</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('admin_scripts')
<script>
    // SweetAlert2 สำหรับยืนยันการลบข้อมูล (เชื่อมกับ Library ใน Layout ที่คุณให้มาแล้ว)
    function confirmDelete(id) {
        Swal.fire({
            title: 'ยืนยันการลบข้อมูล?',
            text: "ข้อมูลนี้จะถูกย้ายลงถังขยะ (Soft Delete) และไม่แสดงผลหน้าเว็บไซต์",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-trash me-1"></i> ใช่, ยืนยันการลบ',
            cancelButtonText: 'ยกเลิก',
            reverseButtons: true,
            customClass: {
                confirmButton: 'btn btn-danger mx-2',
                cancelButton: 'btn btn-secondary mx-2'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }

    // สคริปต์เพื่อให้ Modal แสดงผลค้างไว้หากมี Error จากการ Submit ฟอร์มสร้างใหม่
    @if($errors->any() && !old('_method'))
        document.addEventListener("DOMContentLoaded", function() {
            var createModal = new bootstrap.Modal(document.getElementById('createModal'));
            createModal.show();
        });
    @endif
</script>
@endpush