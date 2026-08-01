@extends('layouts.admin')

@section('title', 'จัดการข้อร้องเรียนและข้อเสนอแนะ')

@section('admin_content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-1 text-dark fw-bold"><i class="bi bi-chat-left-text text-warning me-2"></i>ข้อร้องเรียน และข้อเสนอแนะ</h2>
            <p class="text-muted small mb-0">ตรวจสอบเรื่องร้องเรียน ข้อเสนอแนะ และความคิดเห็นจากประชาชน</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-3xs mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filter & Search Bar -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.feedbacks.index') }}" class="row g-2">
                <div class="col-md-3">
                    <select name="status" class="form-select text-secondary" onchange="this.form.submit()">
                        <option value="">-- สถานะทั้งหมด --</option>
                        <option value="PENDING" {{ request('status') == 'PENDING' ? 'selected' : '' }}>รอดำเนินการ (Pending)</option>
                        <option value="PROCESSING" {{ request('status') == 'PROCESSING' ? 'selected' : '' }}>กำลังดำเนินการ (Processing)</option>
                        <option value="RESOLVED" {{ request('status') == 'RESOLVED' ? 'selected' : '' }}>เสร็จสิ้น (Resolved)</option>
                        <option value="REJECTED" {{ request('status') == 'REJECTED' ? 'selected' : '' }}>ยุติเรื่อง (Rejected)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="type" class="form-select text-secondary" onchange="this.form.submit()">
                        <option value="">-- ประเภททั้งหมด --</option>
                        <option value="COMPLAINT" {{ request('type') == 'COMPLAINT' ? 'selected' : '' }}>ข้อร้องเรียน</option>
                        <option value="SUGGESTION" {{ request('type') == 'SUGGESTION' ? 'selected' : '' }}>ข้อเสนอแนะ</option>
                        <option value="FEEDBACK" {{ request('type') == 'FEEDBACK' ? 'selected' : '' }}>ความคิดเห็น</option>
                        <option value="GENERAL" {{ request('type') == 'GENERAL' ? 'selected' : '' }}>สอบถามทั่วไป</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="ค้นหา Ticket No, หัวข้อ หรือ ชื่อผู้ส่ง..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-primary fw-bold"><i class="bi bi-search me-1"></i> ค้นหา</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-secondary small text-uppercase">
                    <tr>
                        <th class="ps-4">Ticket No.</th>
                        <th>ประเภท</th>
                        <th>หัวข้อเรื่อง / ผู้ส่ง</th>
                        <th>วันที่ส่ง</th>
                        <th class="text-center">สถานะ</th>
                        <th class="text-end pe-4">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($feedbacks as $fb)
                        <tr>
                            <td class="ps-4 font-monospace fw-bold text-primary">{{ $fb->ticket_no }}</td>
                            <td>
                                @if($fb->type == 'COMPLAINT')
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">ข้อร้องเรียน</span>
                                @elseif($fb->type == 'SUGGESTION')
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25">ข้อเสนอแนะ</span>
                                @elseif($fb->type == 'FEEDBACK')
                                    <span class="badge bg-purple text-white">ความคิดเห็น</span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">สอบถามทั่วไป</span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-bold text-dark mb-0">{{ $fb->subject }}</div>
                                <small class="text-muted"><i class="bi bi-person me-1"></i>{{ $fb->fullname }} @if($fb->phone) ({{ $fb->phone }}) @endif</small>
                            </td>
                            <td class="small text-muted">{{ $fb->created_at->format('d/m/Y H:i') }} น.</td>
                            <td class="text-center">
                                @if($fb->status == 'PENDING')
                                    <span class="badge bg-warning text-dark px-2.5 py-1.5 rounded-pill">รอดำเนินการ</span>
                                @elseif($fb->status == 'PROCESSING')
                                    <span class="badge bg-info text-white px-2.5 py-1.5 rounded-pill">กำลังดำเนินการ</span>
                                @elseif($fb->status == 'RESOLVED')
                                    <span class="badge bg-success text-white px-2.5 py-1.5 rounded-pill">เสร็จสิ้น</span>
                                @else
                                    <span class="badge bg-secondary text-white px-2.5 py-1.5 rounded-pill">ยุติเรื่อง</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <button type="button" class="btn btn-sm btn-outline-primary border-0" data-bs-toggle="modal" data-bs-target="#detailModal{{ $fb->id }}">
                                    <i class="bi bi-eye fs-6"></i> เปิดดู
                                </button>
                                <form action="{{ route('admin.feedbacks.destroy', $fb->id) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการลบรายการนี้?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger border-0"><i class="bi bi-trash fs-6"></i></button>
                                </form>
                            </td>
                        </tr>

                        <!-- Modal Detail & Action -->
                        <div class="modal fade" id="detailModal{{ $fb->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content border-0 rounded-4">
                                    <form action="{{ route('admin.feedbacks.update', $fb->id) }}" method="POST">
                                        @csrf @method('PUT')
                                        <div class="modal-header border-bottom pb-3">
                                            <h5 class="modal-title fw-bold">
                                                <i class="bi bi-file-earmark-text text-primary me-2"></i>รายละเอียดคำร้อง : <span class="font-monospace text-primary">{{ $fb->ticket_no }}</span>
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <div class="row g-3 mb-3 bg-light p-3 rounded-3">
                                                <div class="col-md-6">
                                                    <small class="text-muted d-block">ชื่อ-นามสกุล ผู้ส่ง:</small>
                                                    <strong class="text-dark">{{ $fb->fullname }}</strong>
                                                </div>
                                                <div class="col-md-3">
                                                    <small class="text-muted d-block">เบอร์โทรศัพท์:</small>
                                                    <strong class="text-dark">{{ $fb->phone ?? '-' }}</strong>
                                                </div>
                                                <div class="col-md-3">
                                                    <small class="text-muted d-block">อีเมล:</small>
                                                    <strong class="text-dark">{{ $fb->email ?? '-' }}</strong>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <small class="text-muted d-block">หัวข้อเรื่อง:</small>
                                                <h6 class="fw-bold text-dark">{{ $fb->subject }}</h6>
                                            </div>

                                            <div class="mb-4">
                                                <small class="text-muted d-block mb-1">รายละเอียดข้อความ:</small>
                                                <div class="p-3 bg-white border rounded-3 lh-base text-secondary" style="white-space: pre-line;">{{ $fb->message }}</div>
                                            </div>

                                            <hr class="my-3">

                                            <div class="mb-3">
                                                <label class="form-label fw-bold text-dark">อัปเดตสถานะการดำเนินการ <span class="text-danger">*</span></label>
                                                <select name="status" class="form-select fw-bold">
                                                    <option value="PENDING" {{ $fb->status == 'PENDING' ? 'selected' : '' }}>รอดำเนินการ (Pending)</option>
                                                    <option value="PROCESSING" {{ $fb->status == 'PROCESSING' ? 'selected' : '' }}>กำลังดำเนินการ (Processing)</option>
                                                    <option value="RESOLVED" {{ $fb->status == 'RESOLVED' ? 'selected' : '' }}>ดำเนินการเสร็จสิ้น (Resolved)</option>
                                                    <option value="REJECTED" {{ $fb->status == 'REJECTED' ? 'selected' : '' }}>ยุติเรื่อง / ไม่อนุมัติ (Rejected)</option>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-bold text-dark">บันทึกของเจ้าหน้าที่ (Admin Note)</label>
                                                <textarea name="admin_note" class="form-control" rows="3" placeholder="ระบุรายละเอียดการประสานงานหรือข้อสรุป...">{{ $fb->admin_note }}</textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-top pt-3">
                                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">ปิดหน้าต่าง</button>
                                            <button type="submit" class="btn btn-sm btn-primary fw-bold px-4">บันทึกการอัปเดต</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr><td colspan="6" class="text-center py-5 text-muted">ไม่พบรายการข้อร้องเรียนหรือข้อเสนอแนะ</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($feedbacks->hasPages())
            <div class="card-footer bg-white border-0 py-3">{{ $feedbacks->links() }}</div>
        @endif
    </div>
</div>
@endsection