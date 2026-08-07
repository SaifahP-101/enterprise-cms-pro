@extends('layouts.admin')

@section('title', 'ปรับปรุงบทบาทและสิทธิ์การใช้งาน (Edit Role)')

@push('admin_styles')
<style>
    /* 🎨 Module Card & Border Highlights (โทนสีม่วงสำหรับหน้าแก้ไข) */
    .module-card {
        border-left: 4px solid #4C1D95 !important; /* TRU Purple */
        transition: all 0.2s ease-in-out;
    }
    .module-card:hover {
        box-shadow: 0 6px 20px rgba(76, 29, 149, 0.08) !important;
    }

    /* 🏷️ Interactive Checkbox Item Box */
    .perm-item-box {
        transition: all 0.2s ease;
        border: 1px solid #E2E8F0;
        background-color: #F8FAFC;
        cursor: pointer;
    }
    .perm-item-box:hover {
        background-color: #F3E8FF; /* Light Lavender */
        border-color: #C084FC;
    }
    .perm-item-box.active-checked {
        background-color: #F3E8FF;
        border-color: #4C1D95;
    }

    /* 🔘 Custom Checkbox Accent */
    .form-check-input:checked {
        background-color: #4C1D95; /* TRU Purple */
        border-color: #4C1D95;
    }

    /* 📏 Custom Button Extra Small Utility */
    .btn-xs {
        padding: 0.25rem 0.6rem;
        font-size: 0.75rem;
        border-radius: 0.375rem;
    }

    /* 📌 Sticky Behavior Manager (ทำ Sticky เฉพาะบน Desktop) */
    @media (min-width: 992px) {
        .sticky-lg-custom {
            position: sticky;
            top: 85px;
            z-index: 10;
        }
    }
</style>
@endpush

@section('admin_content')
<div class="container-fluid">
    
    <!-- 📌 Header Section -->
    <div class="mb-4">
        <a href="{{ route('admin.roles.index') }}" class="text-decoration-none small text-secondary font-body">
            ← กลับสู่ระบบบริหารจัดการบทบาท
        </a>
        <div class="d-flex align-items-center mt-2 gap-2">
            <h2 class="h4 mb-0 text-dark fw-bold font-heading">
                <i class="bi bi-pencil-square text-primary me-2"></i>ปรับปรุงสิทธิ์บทบาท: <span class="text-primary">{{ $role->name }}</span>
            </h2>
            <span class="badge bg-light text-secondary border font-monospace px-2 py-1">{{ $role->slug }}</span>
        </div>
        <p class="text-muted small font-body mt-1 mb-0">
            ปรับเปลี่ยนชื่อบทบาท คำอธิบาย และจัดการสิทธิ์การเข้าถึงโมดูลต่างๆ (Permission Matrix)
        </p>
    </div>

    <!-- 📌 Success Alert -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-2xs rounded-3 mb-4 font-body" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- 📌 Edit Form Workspace -->
    <form action="{{ route('admin.roles.update', $role->id) }}" method="POST" id="editRoleForm">
        @csrf
        @method('PUT')
        
        <div class="row g-4">
            
            <!-- 👈 ฝั่งซ้าย: ข้อมูลพื้นฐานบทบาท (Sticky Panel) -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm p-4 rounded-4 bg-white sticky-lg-custom">
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3 font-heading">
                        <i class="bi bi-info-circle text-primary me-1"></i> รายละเอียดบทบาท
                    </h6>

                    <!-- ชื่อบทบาท -->
                    <div class="mb-3">
                        <label for="name" class="form-label small fw-bold text-secondary font-heading">
                            ชื่อบทบาทการทำงาน <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control shadow-none @error('name') is-invalid @enderror font-body {{ $role->slug === 'super_admin' ? 'bg-light' : '' }}" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $role->name) }}" 
                               {{ $role->slug === 'super_admin' ? 'readonly' : 'required' }}>
                        
                        @if($role->slug === 'super_admin')
                            <small class="text-danger font-body d-block mt-1">
                                <i class="bi bi-shield-lock-fill"></i> ระบบไม่อนุญาตให้เปลี่ยนชื่อบทบาทผู้ดูแลระบบสูงสุด
                            </small>
                        @endif

                        @error('name')
                            <div class="invalid-feedback font-body">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- คำอธิบายบทบาท -->
                    <div class="mb-4">
                        <label for="description" class="form-label small fw-bold text-secondary font-heading">
                            คำอธิบายหน้าที่และขอบเขตสิทธิ์
                        </label>
                        <textarea class="form-control shadow-none font-body" 
                                  id="description" 
                                  name="description" 
                                  rows="4" 
                                  placeholder="ระบุขอบเขตความรับผิดชอบของบทบาทนี้...">{{ old('description', $role->description) }}</textarea>
                    </div>

                    <!-- ปุ่มการทำงาน -->
                    <div class="d-flex gap-2 pt-2 border-top">
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-sm btn-secondary w-50 py-2 fw-medium font-heading">
                            ยกเลิก
                        </a>
                        <button type="submit" class="btn btn-sm btn-primary w-50 py-2 shadow-sm fw-bold font-heading">
                            <i class="bi bi-floppy me-1"></i> อัปเดตข้อมูล
                        </button>
                    </div>
                </div>
            </div>

            <!-- 👉 ฝั่งขวา: สิทธิ์การใช้งานแบ่งตามโมดูล (Permission Matrix) -->
            <div class="col-lg-8">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                    <h6 class="fw-bold text-dark mb-0 font-heading">
                        <i class="bi bi-list-check text-primary me-1"></i> ผูกสิทธิ์การเข้าถึงระบบ (Permission Matrix)
                    </h6>
                    <button type="button" class="btn btn-xs btn-outline-dark px-3 py-1.5 rounded-pill font-heading" id="btnToggleAll">
                        <i class="bi bi-check-all me-1"></i> เลือกทั้งหมด / ยกเลิกทั้งหมด
                    </button>
                </div>

                <div class="row g-3">
                    @php
                        // ดึง ID ของสิทธิ์ที่เคยเลือกไว้ (รองรับกรณี Validation Error จะคืนค่าตัวเก่ากลับมา)
                        $selectedPerms = old('permissions', $attachedPermissionIds ?? []);
                    @endphp

                    @forelse($permissionsByModule as $module => $permissions)
                        <div class="col-12">
                            <div class="card module-card border-0 shadow-sm p-3 p-md-4 rounded-3 bg-white">
                                
                                <!-- Module Header -->
                                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                    <span class="fw-bold text-dark text-uppercase small font-heading">
                                        <i class="bi bi-grid-fill text-primary me-1.5"></i> โมดูล: 
                                        <span class="text-primary">{{ $module }}</span>
                                        <span class="badge bg-light text-secondary border rounded-pill ms-1 fw-normal">
                                            {{ count($permissions) }} สิทธิ์
                                        </span>
                                    </span>
                                    <button type="button" 
                                            class="btn btn-xs btn-link text-decoration-none p-0 text-muted font-heading btn-select-module" 
                                            data-module="{{ $module }}">
                                        <i class="bi bi-check2-square me-1"></i>เลือกทั้งโมดูลนี้
                                    </button>
                                </div>

                                <!-- Permission Items Grid -->
                                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-2.5">
                                    @foreach($permissions as $perm)
                                        @php
                                            // เช็กว่าไอดีของ Permission นี้ถูกเลือกไว้หรือไม่
                                            $isChecked = in_array($perm->id, $selectedPerms);
                                        @endphp
                                        <div class="col">
                                            <div class="perm-item-box p-2.5 rounded-3 d-flex align-items-start gap-2 h-100 {{ $isChecked ? 'active-checked' : '' }}">
                                                <div class="pt-0.5">
                                                    <input class="form-check-input perm-checkbox perm-module-{{ $module }}" 
                                                           type="checkbox" 
                                                           name="permissions[]" 
                                                           value="{{ $perm->id }}" 
                                                           id="perm_{{ $perm->id }}" 
                                                           {{ $isChecked ? 'checked' : '' }}>
                                                </div>
                                                <label class="form-check-label small font-body w-100" for="perm_{{ $perm->id }}" style="cursor: pointer;">
                                                    <span class="fw-bold text-dark d-block leading-tight mb-0.5">{{ $perm->name }}</span>
                                                    @if($perm->description)
                                                        <small class="d-block text-muted style-italic lh-sm" style="font-size: 0.73rem;">
                                                            {{ $perm->description }}
                                                        </small>
                                                    @endif
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-5 bg-white rounded-4 shadow-sm">
                            <i class="bi bi-shield-slash text-muted opacity-25" style="font-size: 3rem;"></i>
                            <p class="text-muted mb-0 mt-2 font-body">ยังไม่มีรายการ Permissions ในระบบ</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </form>
</div>
@endsection

@push('admin_scripts')
<script>
    $(document).ready(function() {
        // 1. ฟังก์ชันไฮไลต์กรอบกล่องเมื่อ Checkbox ถูกติ๊ก
        function updateItemBoxStyle(checkbox) {
            var itemBox = $(checkbox).closest('.perm-item-box');
            if ($(checkbox).is(':checked')) {
                itemBox.addClass('active-checked');
            } else {
                itemBox.removeClass('active-checked');
            }
        }

        // ฟังอีเวนต์เมื่อมีการคลิกเปลี่ยนสถานะ Checkbox
        $(document).on('change', '.perm-checkbox', function() {
            updateItemBoxStyle(this);
        });

        // ตรวจสอบและไฮไลต์กล่องในจังหวะโหลดหน้าเว็บครั้งแรก
        $('.perm-checkbox').each(function() {
            updateItemBoxStyle(this);
        });

        // 2. ปุ่ม เลือกทั้งหมด / ยกเลิกทั้งหมด (System Wide)
        var globalToggleState = false;
        $('#btnToggleAll').click(function() {
            globalToggleState = !globalToggleState;
            $('.perm-checkbox').prop('checked', globalToggleState).trigger('change');
            
            if (globalToggleState) {
                $(this).html('<i class="bi bi-x-circle me-1"></i> ยกเลิกทั้งหมด');
            } else {
                $(this).html('<i class="bi bi-check-all me-1"></i> เลือกทั้งหมด');
            }
        });

        // 3. ปุ่ม เลือกทั้งหมดประจำแต่ละโมดูล (Module Specific)
        $('.btn-select-module').click(function(e) {
            e.preventDefault();
            var moduleName = $(this).data('module');
            var checkboxes = $('.perm-module-' + moduleName);
            // ตรวจสอบว่าโมดูลนั้นติ๊กครบทุกอันหรือยัง
            var allChecked = checkboxes.filter(':checked').length === checkboxes.length;

            // สลับสถานะของทุกอันในโมดูลนั้น
            checkboxes.prop('checked', !allChecked).trigger('change');
        });
    });
</script>
@endpush