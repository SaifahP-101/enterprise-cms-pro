@extends('layouts.admin')

@section('title', 'จัดการข้อมูลการยืมอุปกรณ์')

@section('styles')
<style>
    /* ปรับแต่ง DataTables ให้ตรงกับ Design อ้างอิง */
    .table-custom-striped tbody tr:nth-of-type(odd) {
        background-color: #fcfcfc;
    }
    .table-custom-striped tbody tr:nth-of-type(even) {
        background-color: #f4f5f7;
    }
    .table-custom-striped thead th {
        background-color: #f8f9fa;
        color: #333;
        border-bottom: 2px solid #e0e0e0;
        font-weight: 600;
        font-size: 0.9rem;
        padding: 12px 10px;
    }
    .table-custom-striped td {
        vertical-align: middle;
        padding: 12px 10px;
        border-bottom: 1px solid #f0f0f0;
        font-size: 0.95rem;
    }
    .text-id-blue {
        color: #0d6efd;
        font-weight: 700;
    }
    .btn-action-outline {
        border-radius: 6px;
        padding: 4px 12px;
        font-size: 0.85rem;
        font-weight: 500;
        background-color: transparent;
    }
    .btn-edit-outline {
        color: #0d6efd;
        border: 1px solid #0d6efd;
    }
    .btn-edit-outline:hover {
        background-color: #0d6efd;
        color: #fff;
    }
    .btn-delete-outline {
        color: #dc3545;
        border: 1px solid #dc3545;
    }
    .btn-delete-outline:hover {
        background-color: #dc3545;
        color: #fff;
    }
    /* ปรับแต่ง Search Box ของ DataTables ให้เหมือนในภาพ */
    .dataTables_filter input {
        border-radius: 6px;
        border: 1px solid #ced4da;
        padding: 4px 10px;
        margin-left: 10px;
    }
    .dataTables_length select {
        border-radius: 6px;
        border: 1px solid #ced4da;
        padding: 4px 30px 4px 10px;
    }
</style>
@endsection

@section('admin_content')
<div class="container-fluid animate__animated animate__fadeIn">
    
    <!-- Header Section -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="h4 mb-1 text-dark fw-bold" style="font-family: 'Kanit', sans-serif;">
                <i class="bi bi-folder2-open text-dark me-2"></i>ระบบจัดการการยืม-คืนอุปกรณ์ (Equipment Borrows)
            </h2>
            <p class="text-muted small mb-0">ตรวจสอบ จัดการ และส่งออกข้อมูลประวัติการยืมอุปกรณ์ประกอบการแสดงและครุภัณฑ์</p>
        </div>
        
        <!-- กลุ่มปุ่มจัดการด้านขวาบน -->
        <div class="d-flex gap-2 flex-wrap">
            <!-- ปุ่มเปิด Modal QR Code -->
            <button type="button" class="btn btn-outline-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#qrCodeModal" style="border-radius: 6px;">
                <i class="bi bi-qr-code-scan me-1"></i> QR Code ลงทะเบียน
            </button>

            <!-- ปุ่มดาวน์โหลด Excel -->
            <a href="{{ route('admin.borrows.export', request()->all()) }}" class="btn btn-success fw-bold shadow-sm" style="background-color: #198754; border-color: #198754; border-radius: 6px;">
                <i class="bi bi-file-earmark-excel me-1"></i> ดาวน์โหลดรายงาน Excel
            </a>
        </div>
    </div>

    <!-- Date Filter Bar -->
    <div class="bg-white p-3 rounded-4 shadow-sm mb-4 border border-light-subtle">
        <form action="{{ route('admin.borrows.index') }}" method="GET" class="row gx-3 gy-2 align-items-center m-0">
            <div class="col-auto">
                <span class="fw-bold small text-muted"><i class="bi bi-funnel"></i> ตัวกรองรายงาน:</span>
            </div>
            <div class="col-auto">
                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}" title="วันที่ยืม (เริ่ม)">
            </div>
            <div class="col-auto"><span class="small text-muted">ถึง</span></div>
            <div class="col-auto">
                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}" title="วันที่ยืม (สิ้นสุด)">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary fw-bold px-3">กรองข้อมูล</button>
            </div>
            @if(request('start_date') || request('end_date'))
                <div class="col-auto">
                    <a href="{{ route('admin.borrows.index') }}" class="btn btn-sm btn-light text-danger fw-bold"><i class="bi bi-x-circle"></i> ล้างตัวกรอง</a>
                </div>
            @endif
        </form>
    </div>

    <!-- Table Card Section -->
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-custom-striped w-100" id="borrowsDataTable">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 5%;">ลำดับอ้างอิง</th>
                            <th style="width: 20%;">ชื่อผู้ยืม / เบอร์โทรศัพท์</th>
                            <th style="width: 15%;">คณะ / สถานะการยืม</th>
                            <th style="width: 22%;">รายการอุปกรณ์ (จำนวน)</th>
                            <th class="text-center" style="width: 13%;">วันที่ยืม-คืน</th>
                            <th class="text-center" style="width: 10%;">รูปถ่ายแนบ</th>
                            <th class="text-center" style="width: 15%;">การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($borrows as $borrow)
                        <tr>
                            <td class="text-center text-id-blue">{{ $borrow->id }}</td>
                            
                            <td>
                                <span class="fw-bold text-dark">{{ $borrow->borrower_name }}</span><br>
                                <span class="text-muted small"><i class="bi bi-telephone text-secondary"></i> {{ $borrow->phone_number }}</span>
                            </td>
                            
                            <td>
                                <span class="d-block text-dark small fw-bold mb-1">{{ $borrow->faculty_department }}</span>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 rounded-1 fw-bold" style="font-size: 0.75rem;">
                                    {{ $borrow->borrower_status }}
                                </span>
                            </td>
                            
                            <td>
                                <span class="text-dark">{{ $borrow->equipment_name }}</span>
                                <span class="badge bg-light text-dark border ms-1">x{{ $borrow->quantity }}</span>
                                @if($borrow->purpose)
                                    <div class="small text-muted mt-1 text-truncate" style="max-width: 250px;" title="{{ $borrow->purpose }}">
                                        {{ $borrow->purpose }}
                                    </div>
                                @endif
                            </td>
                            
                            <td class="text-center small">
                                <!-- Hidden format for DataTables Sorting -->
                                <span class="d-none">{{ $borrow->borrow_date->format('Ymd') }}</span>
                                <span class="d-block text-primary fw-bold">{{ $borrow->borrow_date->format('d/m/Y') }}</span>
                                <span class="d-block text-danger fw-bold mt-1">{{ $borrow->expected_return_date->format('d/m/Y') }}</span>
                            </td>
                            
                            <td class="text-center">
                                @if($borrow->image_path)
                                    <button type="button" class="btn btn-sm btn-light border text-primary" data-bs-toggle="modal" data-bs-target="#imageModal{{ $borrow->id }}" title="ดูรูปภาพ">
                                        <i class="bi bi-image"></i>
                                    </button>

                                    <!-- Modal ดูรูปภาพแนบ -->
                                    <div class="modal fade" id="imageModal{{ $borrow->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow">
                                                <div class="modal-header bg-light">
                                                    <h5 class="modal-title fw-bold" style="font-family: 'Kanit', sans-serif;">รูปภาพ: {{ $borrow->equipment_name }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body text-center p-3">
                                                    <img src="{{ asset('storage/' . $borrow->image_path) }}" class="img-fluid rounded shadow-sm" alt="รูปภาพอุปกรณ์">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <button type="button" class="btn btn-action-outline btn-edit-outline" onclick="alert('ฟีเจอร์นี้สงวนไว้สำหรับการพัฒนาในอนาคต')">
                                        แก้ไข
                                    </button>

                                    <form action="{{ route('admin.borrows.destroy', $borrow->id) }}" method="POST" onsubmit="return confirm('ยืนยันการลบข้อมูลนี้ลงถังขยะ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-action-outline btn-delete-outline">
                                            ลบ
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Fallback Pagination (Backend) -->
            @if(method_exists($borrows, 'links'))
            <div class="d-flex justify-content-end mt-3 d-none" id="laravelPagination">
                {{ $borrows->links('vendor.pagination.bootstrap-5') }}
            </div>
            @endif

        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- Modal: QR Code Generator & Downloader -->
<!-- ============================================== -->
<div class="modal fade" id="qrCodeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-light border-0 pt-4 px-4 pb-0">
                <h5 class="modal-title fw-bold text-dark" style="font-family: 'Kanit', sans-serif;">
                    <i class="bi bi-qr-code-scan text-primary me-2"></i>QR Code ลงทะเบียน
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4">
                <div class="bg-white p-2 rounded-3 border d-inline-block shadow-sm mb-3">
                    <!-- สร้าง QR Code ด้วย Simple-Qrcode แบบ SVG -->
                    {!! QrCode::size(200)->generate(route('borrow.create')) !!}
                </div>
                <p class="text-muted small mb-0 lh-sm">
                    ผู้ยืมสามารถสแกน QR Code นี้<br>เพื่อเข้าสู่หน้าแบบฟอร์มบันทึกการยืม
                </p>
            </div>
            <div class="modal-footer border-0 bg-light justify-content-center pb-4">
                @php
                    // เจนเรท QR Code ขนาดใหญ่ความคมชัดสูงสำหรับปรินต์ แล้วแปลงเป็น Base64
                    $qrSvgString = (string) QrCode::size(1000)->generate(route('borrow.create'));
                    $qrBase64 = base64_encode($qrSvgString);
                @endphp
                <!-- ปุ่มดาวน์โหลดโดยใช้ Data URI Scheme (ไม่ต้องมี Route รองรับ) -->
                <a href="data:image/svg+xml;base64,{{ $qrBase64 }}" download="QR_Borrow_Registration.svg" class="btn btn-primary fw-bold rounded-pill px-4 shadow-sm w-100">
                    <i class="bi bi-download me-1"></i> ดาวน์โหลด (ไฟล์ SVG)
                </a>
            </div>
        </div>
    </div>
</div>

@endsection

@push('admin_scripts')
<script>
    $(document).ready(function() {
        $('#borrowsDataTable').DataTable({
            "language": {
                "sProcessing": "กำลังดำเนินการ...",
                "sLengthMenu": "แสดง _MENU_ แถว",
                "sZeroRecords": "ไม่พบข้อมูลในระบบ",
                "sInfo": "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
                "sInfoEmpty": "แสดง 0 ถึง 0 จาก 0 รายการ",
                "sInfoFiltered": "(กรองข้อมูล _MAX_ ทุกรายการ)",
                "sSearch": "<span class='fw-bold text-dark'>ค้นหาข้อมูลด่วน:</span>",
                "oPaginate": {
                    "sFirst": "หน้าแรก",
                    "sPrevious": "ก่อนหน้า",
                    "sNext": "ถัดไป",
                    "sLast": "หน้าสุดท้าย"
                }
            },
            "order": [[ 4, "desc" ]], // เรียงตามวันที่ยืม-คืน (คอลัมน์ Index 4)
            "columnDefs": [
                { "orderable": false, "targets": [5, 6] } // ปิด Sorting คอลัมน์รูปภาพและปุ่มจัดการ
            ],
            "paging": true,
            "pageLength": 10,
            "info": true,
            "responsive": true
        });
    });
</script>
@endpush