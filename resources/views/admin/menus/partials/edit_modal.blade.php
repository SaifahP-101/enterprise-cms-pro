<div class="modal fade" id="editMenuModal-{{ $item->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.menus.update', $item->id) }}" method="POST" class="modal-content border-0 shadow-lg">
            @csrf @method('PUT')
            <div class="modal-header bg-light border-bottom py-3 px-4">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-pencil-square"></i> ปรับปรุงแก้ไขรายการเมนู</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark"><i class="bi bi-pencil-square"></i> ข้อความเมนูแสดงผล *</label>
                    <input type="text" name="title" class="form-control" value="{{ $item->title }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark"> เส้นทางปลายทาง (URL / Path)</label>
                    <input type="text" name="url" class="form-control" value="{{ $item->url }}">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary"><i class="bi bi-building-columns"></i> เปลี่ยนแปลงสังกัดเมนูแม่หลัก</label>
                    <select name="parent_id" class="form-select">
                        <option value="">-- ตั้งเป็นเมนูหลักสูงสุด (Root Level) --</option>
                        @foreach($parentMenus as $pm)
                            <!-- ป้องกันล็อกเอารหัสไอดีของตัวเองมาสั่งแมปครอบงำตัวเอง -->
                            @if($pm->id != $item->id)
                                <option value="{{ $pm->id }}" {{ $item->parent_id == $pm->id ? 'selected' : '' }}>
                                    <i class="bi bi-pin-angle"></i> สังกัดเป็นกิ่งย่อยของ: {{ $pm->title }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold text-dark"> ลำดับจัดเรียงคีย์</label>
                        <input type="number" name="sort_order" class="form-control" value="{{ $item->sort_order }}" min="0" required>
                    </div>
                    <div class="col-6 d-flex align-items-end pb-1">
                        <div class="form-check form-switch p-3 bg-light rounded border w-100 ms-0">
                            <input class="form-check-input ms-0 me-2" type="checkbox" name="is_active" id="activeEdit-{{ $item->id }}" {{ $item->is_active ? 'checked' : '' }}>
                            <label class="form-check-label small fw-bold text-success" for="activeEdit-{{ $item->id }}">เปิดใช้งานปกติ</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top py-3 px-4">
                <button type="button" class="btn btn-secondary fw-semibold" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" class="btn btn-primary fw-bold px-4 shadow-sm" style="background-color: var(--theme-indigo); border-color: var(--theme-indigo);"><i class="bi bi-floppy"></i> อัปเดตผังเมนู</button>
            </div>
        </form>
    </div>
</div>