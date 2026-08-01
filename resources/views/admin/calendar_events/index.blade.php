@extends('layouts.admin')
@section('title', 'จัดการปฏิทินกิจกรรม')

@section('admin_content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-1 text-dark fw-bold"><i class="bi bi-calendar-check text-primary me-2"></i>จัดการปฏิทินกิจกรรม</h2>
            <p class="text-muted small mb-0">ระบบนัดหมายและตารางกิจกรรมหน้าแรก</p>
        </div>
        <button type="button" class="btn btn-primary fw-bold px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#createEventModal">
            <i class="bi bi-plus-lg me-1"></i> เพิ่มกิจกรรมใหม่
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-3xs mb-4">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-secondary small text-uppercase">
                    <tr>
                        <th class="ps-4">วันที่กิจกรรม</th>
                        <th>หัวข้อกิจกรรม / สถานที่</th>
                        <th>เวลา</th>
                        <th class="text-center">สถานะ</th>
                        <th class="text-end pe-4">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($events as $event)
                        <tr>
                            <td class="ps-4 fw-bold {{ $event->event_date->isFuture() ? 'text-primary' : 'text-secondary' }}">
                                {{ $event->event_date->format('d/m/Y') }}
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $event->title }}</div>
                                <small class="text-muted"><i class="bi bi-geo-alt me-1"></i>{{ $event->location ?? 'ไม่ระบุสถานที่' }}</small>
                            </td>
                            <td>
                                @if($event->start_time)
                                    <span class="badge bg-light text-dark border">
                                        {{ \Carbon\Carbon::parse($event->start_time)->format('H:i') }} 
                                        {{ $event->end_time ? '- ' . \Carbon\Carbon::parse($event->end_time)->format('H:i') : 'น.' }}
                                    </span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($event->is_active)
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill">แสดงผล</span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill">ซ่อน</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <button type="button" class="btn btn-sm btn-outline-warning border-0" data-bs-toggle="modal" data-bs-target="#editEventModal{{ $event->id }}"><i class="bi bi-pencil-square fs-6"></i></button>
                                <form action="{{ route('admin.calendar-events.destroy', $event->id) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันลบกิจกรรม?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger border-0"><i class="bi bi-trash fs-6"></i></button>
                                </form>
                            </td>
                        </tr>

                        <!-- Modal Edit -->
                        <div class="modal fade" id="editEventModal{{ $event->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered text-start">
                                <div class="modal-content border-0 rounded-4">
                                    <form action="{{ route('admin.calendar-events.update', $event->id) }}" method="POST">
                                        @csrf @method('PUT')
                                        <div class="modal-header border-bottom pb-3">
                                            <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square text-warning me-2"></i>แก้ไขกิจกรรม</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold text-secondary">หัวข้อกิจกรรม <span class="text-danger">*</span></label>
                                                <input type="text" name="title" class="form-control" value="{{ $event->title }}" required>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-4">
                                                    <label class="form-label small fw-bold text-secondary">วันที่ <span class="text-danger">*</span></label>
                                                    <input type="date" name="event_date" class="form-control" value="{{ $event->event_date->format('Y-m-d') }}" required>
                                                </div>
                                                <div class="col-4">
                                                    <label class="form-label small fw-bold text-secondary">เวลาเริ่ม</label>
                                                    <input type="time" name="start_time" class="form-control" value="{{ $event->start_time ? \Carbon\Carbon::parse($event->start_time)->format('H:i') : '' }}">
                                                </div>
                                                <div class="col-4">
                                                    <label class="form-label small fw-bold text-secondary">เวลาสิ้นสุด</label>
                                                    <input type="time" name="end_time" class="form-control" value="{{ $event->end_time ? \Carbon\Carbon::parse($event->end_time)->format('H:i') : '' }}">
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold text-secondary">สถานที่</label>
                                                <input type="text" name="location" class="form-control" value="{{ $event->location }}">
                                            </div>
                                            <div class="form-check form-switch mt-3">
                                                <input class="form-check-input" type="checkbox" name="is_active" id="activeEdit{{ $event->id }}" {{ $event->is_active ? 'checked' : '' }}>
                                                <label class="form-check-label fw-bold small" for="activeEdit{{ $event->id }}">แสดงผลหน้าเว็บ</label>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-top pt-3">
                                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                                            <button type="submit" class="btn btn-sm btn-primary fw-bold">อัปเดตข้อมูล</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr><td colspan="5" class="text-center py-5 text-muted">ยังไม่มีกิจกรรมในระบบ</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($events->hasPages()) <div class="card-footer bg-white border-0 py-3">{{ $events->links() }}</div> @endif
    </div>
</div>

<!-- Modal Create -->
<div class="modal fade" id="createEventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4">
            <form action="{{ route('admin.calendar-events.store') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom pb-3">
                    <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle text-primary me-2"></i>เพิ่มกิจกรรมใหม่</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">หัวข้อกิจกรรม <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4">
                            <label class="form-label small fw-bold text-secondary">วันที่ <span class="text-danger">*</span></label>
                            <input type="date" name="event_date" class="form-control" required>
                        </div>
                        <div class="col-4">
                            <label class="form-label small fw-bold text-secondary">เวลาเริ่ม</label>
                            <input type="time" name="start_time" class="form-control">
                        </div>
                        <div class="col-4">
                            <label class="form-label small fw-bold text-secondary">เวลาสิ้นสุด</label>
                            <input type="time" name="end_time" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">สถานที่</label>
                        <input type="text" name="location" class="form-control">
                    </div>
                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" name="is_active" id="activeCreate" checked>
                        <label class="form-check-label fw-bold small" for="activeCreate">แสดงผลหน้าเว็บ</label>
                    </div>
                </div>
                <div class="modal-footer border-top pt-3">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-sm btn-primary fw-bold">บันทึกข้อมูล</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection