@extends('layouts.admin')

@section('title', 'บันทึกประวัติผู้ขอดาวน์โหลดเอกสาร PDF')

@section('admin_content')
<div class="container-fluid animate__animated animate__fadeIn">

    <!-- 1. Header Section -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="h4 mb-1 text-dark fw-bold" style="font-family: 'Kanit', sans-serif;">
                <i class="bi bi-file-earmark-arrow-down-fill text-primary me-2"></i>ประวัติผู้ขอรับบริการดาวน์โหลดเอกสาร PDF
            </h2>
            <p class="text-muted small mb-0">ตรวจสอบและคัดกรองข้อมูลผู้ขอรับบริการสารสนเทศทางวัฒนธรรม ยอดให้บริการรวมและวัตถุประสงค์การนำไปใช้</p>
        </div>
        <div>
            <a href="{{ route('admin.contents.index') }}" class="btn btn-outline-secondary shadow-sm fw-bold px-3 py-2 rounded-3 small">
                <i class="bi bi-arrow-left me-1"></i> กลับสู่คลังบทความหลัก
            </a>
        </div>
    </div>

    <!-- 2. Session Alert Flash Message -->
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show rounded-3 mb-4" role="alert" style="border-left: 4px solid #10b981 !important;">
            <span class="fw-medium"><i class="bi bi-check-circle-fill me-1"></i> สำเร็จ:</span> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- 3. Summary Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 bg-white" style="border-left: 5px solid #3b82f6 !important;">
                <div class="card-body p-3.5 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size: 0.72rem;">จำนวนการดาวน์โหลดทั้งหมด</span>
                        <h3 class="fw-bold mb-0 text-dark" style="font-family: 'Kanit', sans-serif;">
                            {{ number_format($totalLogsCount) }} <span class="text-muted fs-6 fw-normal">ครั้ง</span>
                        </h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded-4 text-primary"><i class="bi bi-cloud-arrow-down-fill fs-3"></i></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 bg-white" style="border-left: 5px solid #e5a91e !important;">
                <div class="card-body p-3.5 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size: 0.72rem;">ตรงตามเงื่อนไขตัวกรอง</span>
                        <h3 class="fw-bold mb-0 text-dark" style="font-family: 'Kanit', sans-serif;">
                            {{ number_format($filteredCount) }} <span class="text-muted fs-6 fw-normal">รายการ</span>
                        </h3>
                    </div>
                    <div class="bg-warning bg-opacity-10 p-3 rounded-4 text-warning"><i class="bi bi-filter-circle-fill fs-3"></i></div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Advanced Filter & Search Bar Card -->
    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white mb-4">
        <form action="{{ route('admin.contents.download_logs') }}" method="GET" class="row g-2 align-items-center">
            
            <!-- Search Keyword -->
            <div class="col-12 col-md-4 col-lg-3">
                <label for="search" class="form-label small fw-bold text-secondary mb-1">คำค้นหา (ชื่อ/อีเมล/หน่วยงาน)</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-secondary-subtle"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" id="search" class="form-control border-secondary-subtle bg-light" placeholder="พิมพ์คำค้นหา..." value="{{ request('search') }}">
                </div>
            </div>

            <!-- Content Select Filter -->
            <div class="col-12 col-md-4 col-lg-3">
                <label for="content_id" class="form-label small fw-bold text-secondary mb-1">เลือกบทความ/สารสนเทศ</label>
                <select name="content_id" id="content_id" class="form-select form-select-sm border-secondary-subtle bg-light">
                    <option value="">-- แสดงทุกบทความ --</option>
                    @foreach($contentList as $c)
                        <option value="{{ $c->id }}" {{ request('content_id') == $c->id ? 'selected' : '' }}>
                            {{ Str::limit($c->title, 40) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Date From -->
            <div class="col-6 col-md-2 col-lg-2">
                <label for="date_from" class="form-label small fw-bold text-secondary mb-1">ตั้งแต่วันที่</label>
                <input type="date" name="date_from" id="date_from" class="form-control form-control-sm border-secondary-subtle bg-light" value="{{ request('date_from') }}">
            </div>

            <!-- Date To -->
            <div class="col-6 col-md-2 col-lg-2">
                <label for="date_to" class="form-label small fw-bold text-secondary mb-1">ถึงวันที่</label>
                <input type="date" name="date_to" id="date_to" class="form-control form-control-sm border-secondary-subtle bg-light" value="{{ request('date_to') }}">
            </div>

            <!-- Buttons -->
            <div class="col-12 col-md-12 col-lg-2 d-flex align-items-end gap-1 mt-3 mt-lg-auto">
                <button type="submit" class="btn btn-sm btn-primary fw-bold px-3 w-100 rounded-3">
                    <i class="bi bi-funnel-fill me-1"></i> กรอง
                </button>
                @if(request()->hasAny(['search', 'content_id', 'date_from', 'date_to']))
                    <a href="{{ route('admin.contents.download_logs') }}" class="btn btn-sm btn-outline-danger px-2.5 rounded-3 text-nowrap" title="ล้างตัวกรอง">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- 5. Download Logs Table Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
        <div class="card-header bg-transparent border-0 py-3 px-4 d-flex justify-content-between align-items-center">
            <h5 class="m-0 fw-bold text-dark font-heading"><i class="bi bi-list-task text-primary me-2"></i>รายการประวัติผู้ขอรับเอกสาร</h5>
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10 px-3 py-1.5 rounded-pill small">
                หน้าที่ {{ $downloadLogs->currentPage() }} / {{ $downloadLogs->lastPage() }}
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-nowrap">
                <thead class="bg-light text-secondary small fw-bold">
                    <tr>
                        <th width="60" class="text-center py-3 ps-4">ID</th>
                        <th class="py-3">ชื่อพาดหัวบทความ / เอกสาร</th>
                        <th class="py-3">ผู้ขอรับเอกสาร</th>
                        <th class="py-3">ช่องทางติดต่อ</th>
                        <th class="py-3">หน่วยงาน / สถาบัน</th>
                        <th width="150" class="py-3">วัตถุประสงค์</th>
                        <th width="140" class="text-center py-3">วัน-เวลาที่ขอ</th>
                        <th width="100" class="text-center py-3 pe-4">การจัดการ</th>
                    </tr>
                </thead>
                <tbody class="border-top-0 small">
                    @forelse($downloadLogs as $log)
                    <tr>
                        <!-- ID -->
                        <td class="text-center py-3 ps-4 fw-bold text-secondary bg-light-subtle">{{ $log->id }}</td>

                        <!-- Title & PDF Path -->
                        <td class="py-3">
                            @if($log->content && !empty($log->content->slug))
                                <a href="{{ route('contents.show', $log->content->slug) }}" target="_blank" class="fw-bold text-dark text-decoration-none hover-purple text-truncate d-block" style="max-width: 260px;" title="{{ $log->content->title }}">
                                    {{ $log->content->title }}
                                </a>
                                <small class="text-muted font-monospace" style="font-size: 0.75rem;">
                                    <i class="bi bi-file-earmark-pdf text-danger"></i> {{ basename($log->content->secure_pdf_path ?? 'document.pdf') }}
                                </small>
                            @elseif($log->content)
                                <span class="fw-bold text-dark text-truncate d-block" style="max-width: 260px;">{{ $log->content->title }}</span>
                                <small class="text-muted font-monospace" style="font-size: 0.75rem;">
                                    <i class="bi bi-file-earmark-pdf text-danger"></i> {{ basename($log->content->secure_pdf_path ?? 'document.pdf') }}
                                </small>
                            @else
                                <span class="text-danger italic"><i class="bi bi-exclamation-triangle me-1"></i>บทความถูกลบแล้ว</span>
                            @endif
                        </td>

                        <!-- Requester Fullname -->
                        <td class="py-3 fw-bold text-dark">
                            <i class="bi bi-person-circle text-primary me-1"></i>{{ $log->fullname }}
                        </td>

                        <!-- Contact Info -->
                        <td class="py-3">
                            @if($log->email)
                                <div class="text-dark"><i class="bi bi-envelope text-muted me-1"></i>{{ $log->email }}</div>
                            @endif
                            @if($log->phone)
                                <div class="text-muted"><i class="bi bi-telephone text-muted me-1"></i>{{ $log->phone }}</div>
                            @endif
                            @if(!$log->email && !$log->phone)
                                <span class="text-muted italic">-</span>
                            @endif
                        </td>

                        <!-- Organization -->
                        <td class="py-3 text-secondary">
                            {{ $log->organization ?? '-' }}
                        </td>

                        <!-- Purpose (Truncated + Trigger Modal) -->
                        <td class="py-3">
                            <span class="d-inline-block text-truncate text-muted" style="max-width: 140px;">
                                {{ $log->purpose ?? 'ไม่ระบุ' }}
                            </span>
                            <button type="button" class="btn btn-link btn-sm text-primary p-0 ms-1 btn-view-detail" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#logDetailModal"
                                    data-id="{{ $log->id }}"
                                    data-fullname="{{ $log->fullname }}"
                                    data-email="{{ $log->email ?? '-' }}"
                                    data-phone="{{ $log->phone ?? '-' }}"
                                    data-org="{{ $log->organization ?? '-' }}"
                                    data-purpose="{{ $log->purpose ?? 'ไม่ได้ระบุวัตถุประสงค์' }}"
                                    data-ip="{{ $log->ip_address ?? '-' }}"
                                    data-agent="{{ $log->user_agent ?? '-' }}"
                                    data-title="{{ $log->content->title ?? 'ไม่ทราบหัวข้อ' }}"
                                    data-date="{{ $log->created_at->format('d/m/Y H:i:s') }}">
                                <i class="bi bi-info-circle-fill"></i>
                            </button>
                        </td>

                        <!-- Date Time -->
                        <td class="text-center py-3 text-muted">
                            <div>{{ $log->created_at->format('d/m/Y') }}</div>
                            <small class="text-black-50" style="font-size: 0.75rem;">{{ $log->created_at->format('H:i:s') }} น.</small>
                        </td>

                        <!-- Action Delete -->
                        <td class="text-center py-3 pe-4">
                            <form action="{{ route('admin.contents.download_logs.destroy', $log->id) }}" method="POST" class="d-inline form-delete-log">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle p-1 px-2" title="ลบเรคคอร์ดนี้">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 text-secondary opacity-40 d-block mb-2"></i>
                            <h6 class="fw-bold text-dark">ไม่พบประวัติการขอรับบริการดาวน์โหลดเอกสาร</h6>
                            <p class="small text-muted mb-0">ยังไม่มีผู้ลงทะเบียนขอรับเอกสาร หรือไม่มีข้อมูลตรงกับเงื่อนไขการกรอง</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 6. Pagination Links -->
    @if($downloadLogs->hasPages())
        <div class="d-flex justify-content-center mb-5">
            {{ $downloadLogs->links('vendor.pagination.bootstrap-5') }}
        </div>
    @endif

</div>

<!-- ========================================================================= -->
<!-- 🔍 MODAL: แสดงรายละเอียดประวัติผู้ขอดาวน์โหลดแบบเต็ม -->
<!-- ========================================================================= -->
<div class="modal fade" id="logDetailModal" tabindex="-1" aria-labelledby="logDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 bg-dark text-white rounded-top-4 py-3 px-4">
                <h5 class="modal-title font-heading fw-bold text-warning" id="logDetailModalLabel">
                    <i class="bi bi-card-heading me-2"></i>รายละเอียดการขอรับบริการเอกสาร
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3 p-3 bg-light rounded-3 border">
                    <small class="text-muted d-block fw-bold mb-1">หัวข้อบทความ / เอกสาร:</small>
                    <div id="modal_content_title" class="fw-bold text-dark"></div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <small class="text-muted d-block fw-bold">ชื่อ-นามสกุล:</small>
                        <div id="modal_fullname" class="text-dark fw-bold"></div>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block fw-bold">วัน-เวลาที่ขอ:</small>
                        <div id="modal_date" class="text-dark"></div>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block fw-bold">อีเมล:</small>
                        <div id="modal_email" class="text-dark"></div>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block fw-bold">เบอร์โทรศัพท์:</small>
                        <div id="modal_phone" class="text-dark"></div>
                    </div>
                    <div class="col-12">
                        <small class="text-muted d-block fw-bold">หน่วยงาน / สถาบัน / คณะ:</small>
                        <div id="modal_org" class="text-dark"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block fw-bold mb-1">วัตถุประสงค์การนำไปใช้ประโยชน์:</small>
                    <div id="modal_purpose" class="p-3 bg-white rounded-3 border text-secondary" style="white-space: pre-line;"></div>
                </div>

                <div class="p-2.5 bg-light rounded-3 small text-muted">
                    <div class="d-flex justify-content-between">
                        <span><strong>IP Address:</strong> <span id="modal_ip"></span></span>
                    </div>
                    <div class="mt-1 text-truncate" style="font-size: 0.75rem;">
                        <strong>User Agent:</strong> <span id="modal_agent"></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0 rounded-bottom-4 px-4 py-2.5">
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4" data-bs-dismiss="modal">ปิดหน้าต่าง</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('admin_scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        
        // 1. ดักจับการคลิกปุ่มดูรายละเอียดเพื่อแสดงใน Modal
        const detailButtons = document.querySelectorAll('.btn-view-detail');
        detailButtons.forEach(button => {
            button.addEventListener('click', function () {
                document.getElementById('modal_content_title').innerText = this.getAttribute('data-title');
                document.getElementById('modal_fullname').innerText = this.getAttribute('data-fullname');
                document.getElementById('modal_email').innerText = this.getAttribute('data-email');
                document.getElementById('modal_phone').innerText = this.getAttribute('data-phone');
                document.getElementById('modal_org').innerText = this.getAttribute('data-org');
                document.getElementById('modal_purpose').innerText = this.getAttribute('data-purpose');
                document.getElementById('modal_ip').innerText = this.getAttribute('data-ip');
                document.getElementById('modal_agent').innerText = this.getAttribute('data-agent');
                document.getElementById('modal_date').innerText = this.getAttribute('data-date');
            });
        });

        // 2. SweetAlert2 ยืนยันการลบเรคคอร์ดประวัติ
        $('.form-delete-log').on('submit', function (e) {
            e.preventDefault();
            var form = this;
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'ยืนยันลบเรคคอร์ดประวัตินี้?',
                    text: "รายการบันทึกข้อมูลผู้ขอดาวน์โหลดนี้จะถูกลบออกจากฐานข้อมูล!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'ใช่, ลบทันที',
                    cancelButtonText: 'ยกเลิก'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            } else {
                if (confirm('ยืนยันลบเรคคอร์ดประวัตินี้?')) {
                    form.submit();
                }
            }
        });

    });
</script>
@endpush