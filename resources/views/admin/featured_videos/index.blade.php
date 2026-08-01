@extends('layouts.admin')

@section('title', 'จัดการวิดีโอแนะนำและกิจกรรมเด่น')

@section('admin_content')
<div class="container-fluid">
    <!-- Header Bar -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-1 text-dark fw-bold"><i class="bi bi-youtube text-danger me-2"></i>จัดการวิดีโอแนะนำและกิจกรรมเด่น</h2>
            <p class="text-muted small mb-0">บริหารจัดการวิดีโอ YouTube ที่นำเสนอในหน้าแรกขององค์กร</p>
        </div>
        <button type="button" class="btn btn-primary fw-bold px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#createVideoModal">
            <i class="bi bi-plus-lg me-1"></i> เพิ่มวิดีโอใหม่
        </button>
    </div>

    <!-- Alert Success -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-3xs mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Data Table Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary small text-uppercase">
                        <tr>
                            <th class="ps-4" style="width: 80px;">ลำดับ</th>
                            <th style="width: 140px;">รูปปก</th>
                            <th>หัวข้อวิดีโอ</th>
                            <th>YouTube ID / URL</th>
                            <th class="text-center" style="width: 110px;">สถานะ</th>
                            <th class="text-end pe-4" style="width: 140px;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($videos as $video)
                            <tr>
                                <td class="ps-4 fw-bold text-secondary">{{ $video->sort_order }}</td>
                                <td>
                                    <div class="rounded-3 overflow-hidden border bg-dark" style="width: 120px; height: 68px;">
                                        <img src="{{ $video->thumbnail_url }}" class="w-100 h-100 object-fit-cover" alt="{{ $video->title }}">
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark mb-1">{{ $video->title }}</div>
                                    <small class="text-muted text-truncate d-block" style="max-width: 300px;">{{ $video->description ?? '-' }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border font-monospace">{{ $video->youtube_id ?? 'N/A' }}</span>
                                    <a href="{{ $video->youtube_url }}" target="_blank" class="small text-decoration-none d-block text-truncate mt-1" style="max-width: 220px;">
                                        <i class="bi bi-box-arrow-up-right me-1"></i> เปิดดูบน YouTube
                                    </a>
                                </td>
                                <td class="text-center">
                                    @if($video->is_active)
                                        <span class="badge bg-success bg-opacity-10 text-success px-2.5 py-1.5 rounded-pill">เปิดใช้งาน</span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary px-2.5 py-1.5 rounded-pill">ปิดใช้งาน</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-warning border-0 px-2 rounded-2" 
                                                data-bs-toggle="modal" data-bs-target="#editVideoModal{{ $video->id }}">
                                            <i class="bi bi-pencil-square fs-6"></i>
                                        </button>
                                        <form action="{{ route('admin.featured-videos.destroy', $video->id) }}" method="POST" onsubmit="return confirm('ยืนยันทำลายรายการวิดีโอนี้?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger border-0 px-2 rounded-2">
                                                <i class="bi bi-trash fs-6"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <!-- MODAL EDIT VIDEO -->
                            <div class="modal fade" id="editVideoModal{{ $video->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 rounded-4 shadow">
                                        <form action="{{ route('admin.featured-videos.update', $video->id) }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header border-bottom pb-3">
                                                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-pencil-square me-2 text-warning"></i>แก้ไขวิดีโอแนะนำ</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body p-4">
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold text-secondary">หัวข้อวิดีโอ <span class="text-danger">*</span></label>
                                                    <input type="text" name="title" class="form-control shadow-2xs" value="{{ $video->title }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold text-secondary">YouTube URL <span class="text-danger">*</span></label>
                                                    <input type="url" name="youtube_url" class="form-control shadow-2xs" value="{{ $video->youtube_url }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold text-secondary">คำอธิบายย่อ</label>
                                                    <textarea name="description" class="form-control shadow-2xs" rows="3">{{ $video->description }}</textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold text-secondary">เปลี่ยนภาพปก Custom (ไม่บังคับ)</label>
                                                    <input type="file" name="custom_thumbnail" class="form-control shadow-2xs" accept="image/*">
                                                </div>
                                                <div class="row g-3">
                                                    <div class="col-6">
                                                        <label class="form-label small fw-bold text-secondary">ลำดับแสดงผล</label>
                                                        <input type="number" name="sort_order" class="form-control shadow-2xs" value="{{ $video->sort_order }}">
                                                    </div>
                                                    <div class="col-6 d-flex align-items-end mb-2">
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" name="is_active" id="activeEdit{{ $video->id }}" {{ $video->is_active ? 'checked' : '' }}>
                                                            <label class="form-check-label small fw-bold text-dark" for="activeEdit{{ $video->id }}">เปิดใช้งาน</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-top pt-3">
                                                <button type="button" class="btn btn-sm btn-secondary px-3" data-bs-dismiss="modal">ยกเลิก</button>
                                                <button type="submit" class="btn btn-sm btn-primary fw-bold px-4">อัปเดตข้อมูล</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-youtube fs-1 text-secondary opacity-25 d-block mb-2"></i>
                                    ยังไม่มีรายการวิดีโอแนะนำในระบบ
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($videos->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $videos->links() }}
            </div>
        @endif
    </div>
</div>

<!-- MODAL CREATE VIDEO -->
<div class="modal fade" id="createVideoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <form action="{{ route('admin.featured-videos.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-bottom pb-3">
                    <h5 class="modal-title fw-bold text-dark"><i class="bi bi-plus-circle me-2 text-primary"></i>เพิ่มวิดีโอแนะนำใหม่</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">หัวข้อวิดีโอ <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control shadow-2xs" placeholder="เช่น แนะนำสำนักศิลปะและวัฒนธรรม มรภ.เทพสตรี" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">YouTube URL <span class="text-danger">*</span></label>
                        <input type="url" name="youtube_url" class="form-control shadow-2xs" placeholder="https://www.youtube.com/watch?v=..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">คำอธิบายย่อ</label>
                        <textarea name="description" class="form-control shadow-2xs" rows="3" placeholder="รายละเอียดวิดีโอสังเขป..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">ภาพปก Custom (ถ้าไม่ใส่จะดึงจาก YouTube อัตโนมัติ)</label>
                        <input type="file" name="custom_thumbnail" class="form-control shadow-2xs" accept="image/*">
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary">ลำดับแสดงผล</label>
                            <input type="number" name="sort_order" class="form-control shadow-2xs" value="0">
                        </div>
                        <div class="col-6 d-flex align-items-end mb-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="activeCreate" checked>
                                <label class="form-check-label small fw-bold text-dark" for="activeCreate">เปิดใช้งาน</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top pt-3">
                    <button type="button" class="btn btn-sm btn-secondary px-3" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-sm btn-primary fw-bold px-4">บันทึกข้อมูล</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection