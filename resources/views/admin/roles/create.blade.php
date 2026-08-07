@extends('layouts.admin')

@section('title', 'สร้างบทบาทใหม่ (Create Role)')

@push('admin_styles')
<style>
    /* 🎨 Module Card & Border Highlights */
    .module-card {
        border-left: 4px solid #DAA520 !important; /* TRU Gold */
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
        <h2 class="h4 mb-1 text-dark fw-bold mt-2 font-heading">
            <i class="bi bi-shield-plus text-primary me-2"></i>สร้างบทบาทการทำงานใหม่
        </h2>
        <p class="text-muted small font-body mb-0">
            กำหนดชื่อบทบาท คำอธิบาย และเลือกจับคู่สิทธิ์การทำงาน (Permissions) ประจำโมดูลเพื่อควบคุมสิทธิ์ผู้ใช้
        </p>
    </div>

    <!-- 📌 Create Form Workspace -->
    <form action="{{ route('admin.roles.store') }}" method="POST" id="createRoleForm">
        @csrf
        
        <div class="row g-4">
            
            <!-- 👈 ฝั่งซ้าย: ข้อมูลพื้นฐานบทบาท (Sticky Panel) -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm p-4 rounded-4 bg-white sticky-lg-custom">
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3 font-heading">
                        <i class="bi bi-info-circle text-warning me-1"></i> รายละเอียดบทบาท
                    </h6>

                    <!-- ชื่อบทบาท -->
                    <div class="mb-3">
                        <label for="name" class="form-label small fw-bold text-secondary font-heading">
                            ชื่อบทบาทการทำงาน <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control shadow-none @error('name') is-invalid @enderror font-body" 
                               id="name" 
                               name="name" 
                               placeholder="เช่น ผู้ตรวจทานบทความ, เจ้าหน้าที่ไอที..." 
                               value="{{ old('name') }}" 
                               required>
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
                                  placeholder="ระบุขอบเขตความรับผิดชอบของบทบาทนี้เพื่อใช้อ้างอิงในองค์กร...">{{ old('description') }}</textarea>
                    </div>

                    <!-- ปุ่มการทำงาน -->
                    <div class="d-flex gap-2 pt-2 border-top">
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-sm btn-secondary w-50 py-2 fw-medium font-heading">
                            ยกเลิก
                        </a>
                        <button type="submit" class="btn btn-sm btn-primary w-50 py-2 shadow-sm fw-bold font-heading">
                            <i class="bi bi-floppy me-1"></i> บันทึกข้อมูล
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
                        <i class="bi bi-check-all me-1"></i> เลือกทั้งหมด
                    </button>
                </div>

                <div class="row g-3">
                    @forelse($permissionsByModule as $module => $permissions)
                        <div class="col-12">
                            <div class="card module-card border-0 shadow-sm p-3 p-md-4 rounded-3 bg-white">
                                
                                <!-- Module Header -->
                                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                    <span class="fw-bold text-dark text-uppercase small font-heading">
                                        <i class="bi bi-grid-fill text-warning me-1.5"></i> โมดูล: 
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
                                            $isChecked = is_array(old('permissions')) && in_array($perm->id, old('permissions'));
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
                            <p class="text-muted mb-0 mt-2 font-body">ยังไม่มีรายการ Permissions ในระบบ กรุณารัน Database Seeder ก่อน</p>
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
        // 1. ฟังก์ชันไฮไลต์กรอบเมื่อ Checkbox ถูกเลือก
        function updateItemBoxStyle(checkbox) {
            var itemBox = $(checkbox).closest('.perm-item-box');
            if ($(checkbox).is(':checked')) {
                itemBox.addClass('active-checked');
            } else {
                itemBox.removeClass('active-checked');
            }
        }

        // ฟังอีเวนต์การเปลี่ยนแปลงของ Checkbox
        $(document).on('change', '.perm-checkbox', function() {
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
            var allChecked = checkboxes.filter(':checked').length === checkboxes.length;

            checkboxes.prop('checked', !allChecked).trigger('change');
        });
    });
</script>
@endpush