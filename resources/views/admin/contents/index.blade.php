@extends('layouts.admin')

@section('title', 'จัดการระบบบทความและข่าวสาร')

@section('admin_content')
<div class="container-fluid">
    <!-- Header Bar -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-1 text-dark fw-bold"><i class="bi bi-pencil-square"></i> ระบบบริหารจัดการบทความและกิจกรรม (Content Ledger)</h2>
            <p class="text-muted small mb-0">บันทึก คัดกรอง และเรียบเรียงข้อมูลข่าวสารเพื่อขับเคลื่อนขึ้นสู่หน้าแรกขององค์กร</p>
        </div>
        <a href="{{ route('admin.contents.create') }}" class="btn btn-sm btn-success px-4 py-2 shadow-sm">
            <i class="bi bi-sparkles"></i> สร้างบทความใหม่
        </a>
    </div>

    <!-- Main Content Card -->
    <div class="card main-content-card shadow-sm border-0">
        <div class="card-body p-4">
            
            <!-- 🔍 ⚡ CATEGORY FILTER BAR COMPONENT ⚡ -->
            <div class="p-3 bg-light rounded-3 border mb-4">
                <form method="GET" action="{{ route('admin.contents.index') }}" id="categoryFilterForm">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-5 col-lg-4">
                            <label class="form-label small fw-bold text-secondary mb-1">
                                <i class="bi bi-funnel-fill text-warning me-1"></i> คัดกรองตามหมวดหมู่หลัก:
                            </label>
                            <select name="category_id" class="form-select form-select-sm shadow-2xs" onchange="this.form.submit()">
                                <option value="">-- แสดงทุกหมวดหมู่สารสนเทศ --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-auto d-flex gap-2">
                            <button type="submit" class="btn btn-sm btn-primary fw-bold px-3">
                                <i class="bi bi-filter"></i> กรองข้อมูล
                            </button>
                            @if(request('category_id'))
                                <a href="{{ route('admin.contents.index') }}" class="btn btn-sm btn-outline-secondary fw-bold px-3">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i> ล้างตัวกรอง
                                </a>
                            @endif
                        </div>

                        @if(request('category_id'))
                            <div class="col-md-12 mt-2">
                                <small class="text-primary fw-semibold">
                                    <i class="bi bi-info-circle-fill me-1"></i> 
                                    กำลังแสดงผลเฉพาะบทความสังกัดหมวดหมู่: 
                                    <strong>"{{ $categories->firstWhere('id', request('category_id'))->name ?? 'ไม่พบหมวดหมู่' }}"</strong>
                                </small>
                            </div>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Data Table -->
            <div class="table-responsive">
                <table id="contentsTable" class="table table-striped align-middle" style="width:100%">
                    <thead class="table-light text-secondary small text-uppercase">
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th>หัวข้อพาดหัวข่าว / ประกาศ</th>
                            <th>หมวดหมู่หลัก</th> 
                            <th>ยอดวิวรวม</th>
                            <th>วันที่ตั้งเผยแพร่</th>
                            <th class="text-end" style="width: 160px;">การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($contents as $item)
                            <tr>
                                <td class="fw-bold text-secondary">#{{ $item->id }}</td>
                                <td>
                                    <span class="d-block fw-semibold text-dark text-truncate" style="max-width: 320px;">{{ $item->title }}</span>
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">URL: /content/{{ $item->slug }}</small>
                                </td>
                                <td>
                                    <span class="badge px-2.5 py-1.5 rounded-pill" style="background-color: {{ $item->category->color_code ?? '#6C757D' }}; text-shadow: 0 1px 2px rgba(0,0,0,0.2);">
                                        {{ $item->category->name }}
                                    </span>
                                </td> 
                                <td><font class="fw-bold text-dark">{{ number_format($item->view_count) }}</font> ครั้ง</td>
                                <td><small class="text-muted">{{ $item->published_at ? $item->published_at->format('d/m/Y H:i') : '-' }} น.</small></td>
                                <td class="text-end">
                                    <a href="{{ route('admin.contents.edit', $item->id) }}" class="btn btn-xs btn-outline-primary me-1 px-2 py-1">แก้ไข</a>
                                    <form action="{{ route('admin.contents.destroy', $item->id) }}" method="POST" class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-xs btn-outline-danger px-2 py-1 btn-delete">ทิ้งถังขยะ</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('admin_scripts')
<script>
    $(document).ready(function() {
        $('#contentsTable').DataTable({
            responsive: true,
            order: [[0, "desc"]],
            language: { 
                search: " ค้นหาบทความด่วน:", 
                lengthMenu: "แสดง _MENU_ รายการต่อหน้า", 
                info: "แสดงลำดับข่าวที่ _START_-_END_ จากทั้งหมด _TOTAL_ ชิ้น",
                zeroRecords: "ไม่พบข้อมูลบทความตรงกับเงื่อนไขการค้นหา/การกรอง"
            }
        });

        $('.btn-delete').click(function(e) {
            e.preventDefault();
            var form = $(this).closest('form');
            Swal.fire({
                title: 'ย้ายบทความลงถังขยะ?',
                text: "ข้อมูลจะถูกซ่อนจากหน้าบ้านชั่วคราว แอดมินสามารถเข้าไปกู้คืนระบบประวัติหลังบ้านได้เสมอ",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#DAA520',
                cancelButtonColor: '#202040',
                confirmButtonText: ' ย้ายลงถังขยะ',
                cancelButtonText: 'เก็บไว้ก่อน'
            }).then((result) => { if (result.isConfirmed) form.submit(); });
        });
    });
</script>
@endpush