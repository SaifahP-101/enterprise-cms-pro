@extends('layouts.admin')

@section('title', 'จัดการข้อมูลสมาชิกและสิทธิ์การใช้งาน')

@push('admin_styles')
<style>
    /* 🏷️ Badge Custom Styling */
    .badge-role {
        background-color: #F3E8FF;
        color: #4C1D95;
        border: 1px solid #E9D5FF;
        font-size: 0.75rem;
        padding: 4px 8px;
        white-space: nowrap;
    }
    
    /* 📏 Button Extra Small Utility Class */
    .btn-xs {
        padding: 0.25rem 0.55rem;
        font-size: 0.75rem;
        border-radius: 0.375rem;
    }

    /* 👤 Avatar Circle Stability Enforcer */
    .avatar-circle {
        width: 36px;
        height: 36px;
        flex-shrink: 0;
        font-size: 0.85rem;
    }

    /* 🎨 Select2 Bootstrap 5 Fixes */
    .select2-container--bootstrap-5 .select2-selection {
        border-color: #CED4DA;
        min-height: 38px;
    }
</style>
@endpush

@section('admin_content')
<div class="container-fluid">
    
    <!-- 📌 ส่วนหัวโมดูล -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="h4 mb-1 text-dark fw-bold font-heading">
                <i class="bi bi-people-fill text-primary me-2"></i>ระบบบริหารจัดการสมาชิก (User Registry Management)
            </h2>
            <p class="text-muted small mb-0 font-body">เพิ่ม แก้ไข กำหนดบทบาท RBAC และมอบหมายขอบเขตหมวดหมู่ข่าวสารสำหรับบุคลากร</p>
        </div>
        <div>
            <button class="btn btn-sm btn-success px-4 py-2 rounded-3 shadow-sm fw-bold font-heading" data-bs-toggle="modal" data-bs-target="#createUserModal">
                <i class="bi bi-person-plus-fill me-1"></i> + เพิ่มบัญชีสมาชิกใหม่
            </button>
        </div>
    </div>

    <!-- 📌 Notification Alerts -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4 font-body" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4 font-body" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- 📌 แผงตารางแสดงรายชื่อบุคลากร (Clean & Valid HTML Table) -->
    <div class="card main-content-card shadow-sm border-0 rounded-4 bg-white mb-4">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="width:100%">
                    <thead class="table-light text-secondary small text-uppercase font-heading">
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th>บุคลากร</th>
                            <th>อีเมลระบบ</th>
                            <th>บทบาทในระบบ (Roles)</th>
                            <th>หมวดหมู่ที่ได้รับมอบหมาย</th>
                            <th>สิทธิ์ Admin</th>
                            <th>วันที่ลงทะเบียน</th>
                            <th class="text-end">การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="font-body">
                        @forelse($users as $user)
                            <tr>
                                <td class="fw-bold text-secondary">#{{ $user->id }}</td>
                                <td>
                                    <div class="d-flex align-items-center"> 
                                        <div>
                                            <span class="fw-semibold text-dark d-block leading-tight">{{ $user->name }}</span>
                                            @if(Auth::id() === $user->id)
                                                <span class="badge bg-info text-dark rounded-pill px-2" style="font-size: 0.65rem;">บัญชีของคุณ</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td><code class="text-dark">{{ $user->email }}</code></td>
                                
                                <!-- 🏷️ แสดงบทบาท (Roles) -->
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        @forelse($user->roles as $role)
                                            <span class="badge bg-primary badge-role rounded-2">{{ $role->name }}</span>
                                        @empty
                                            <span class="text-muted small italic">ไม่มีบทบาท</span>
                                        @endforelse
                                    </div>
                                </td>

                                <!-- 📂 แสดงขอบเขตหมวดหมู่ที่ได้รับมอบหมาย -->
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        @if($user->is_admin || $user->hasRole('super_admin') || $user->hasPermission('manage_all_categories'))
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-2 px-2 py-1">
                                                <i class="bi bi-globe me-1"></i> ทุกหมวดหมู่ (Unrestricted)
                                            </span>
                                        @else
                                            @forelse($user->categories as $cat)
                                                <span class="badge rounded-2" style="background-color: #FEF3C7; color: #92400E; border: 1px solid #FDE68A; font-size: 0.75rem; padding: 4px 8px;">{{ $cat->name }}</span>
                                            @empty
                                                <span class="text-muted small italic">ไม่ได้ระบุ (จำกัดสิทธิ์)</span>
                                            @endforelse
                                        @endif
                                    </div>
                                </td>

                                <!-- 🛡️ แสดงสิทธิ์ผู้ดูแลระบบ -->
                                <td>
                                    @if($user->is_admin)
                                        <span class="badge bg-danger px-2.5 py-1.5 rounded-1 shadow-sm">
                                            <i class="bi bi-shield-check me-1"></i> Admin
                                        </span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-25 text-dark px-2.5 py-1.5 rounded-1">
                                            <i class="bi bi-person me-1"></i> User
                                        </span>
                                    @endif
                                </td>

                                <td><small class="text-muted">{{ $user->created_at ? $user->created_at->format('d/m/Y H:i') : '-' }} น.</small></td>
                                
                                <!-- ⚙️ ปุ่มการจัดการ -->
                                <td class="text-end">
                                    <button class="btn btn-xs btn-outline-primary me-1 rounded-2 font-heading" data-bs-toggle="modal" data-bs-target="#editUserModal{{ $user->id }}">
                                        <i class="bi bi-pencil-square me-0.5"></i> แก้ไข
                                    </button>

                                    @if(Auth::id() !== $user->id)
                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline delete-user-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-xs btn-outline-danger rounded-2 font-heading btn-delete-trigger">
                                                <i class="bi bi-trash me-0.5"></i> ลบ
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-people text-muted opacity-25" style="font-size: 3rem;"></i>
                                    <p class="mb-0 mt-2 font-body">ยังไม่มีข้อมูลบัญชีผู้ใช้งานในระบบ</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Links -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3 pt-3 border-top gap-2">
                <small class="text-muted font-body">แสดง {{ $users->firstItem() ?? 0 }} ถึง {{ $users->lastItem() ?? 0 }} จาก {{ $users->total() }} รายการ</small>
                <div>
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- ✏️ MODALS SECTION: แยกไว้ด้านนอกตารางเพื่อป้องกัน HTML Struct Breakage     -->
<!-- ========================================================================= -->

<!-- ลูปสร้าง Modal แก้ไขสำหรับบุคลากรแต่ละคน -->
@foreach($users as $user)
    <div class="modal fade edit-user-modal" id="editUserModal{{ $user->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-content border-top border-4 border-primary rounded-4">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title fw-bold text-dark fs-6 font-heading">
                            <i class="bi bi-pencil-square text-primary me-1"></i> แก้ไขข้อมูลและสิทธิ์บุคลากร: {{ $user->name }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    
                    <div class="modal-body p-4 font-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-secondary font-heading">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control shadow-none" value="{{ old('name', $user->name) }}" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-secondary font-heading">อีเมลเข้าใช้งาน <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control shadow-none" value="{{ old('email', $user->email) }}" required>
                            </div>

                            <!-- เลือกบทบาทหน้าที่ RBAC Roles -->
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-secondary font-heading">
                                    <i class="bi bi-shield-lock text-primary me-1"></i> บทบาทหน้าที่ในระบบ (RBAC Roles)
                                </label>
                                @php $userRoleIds = $user->roles->pluck('id')->toArray(); @endphp
                                <select name="roles[]" class="form-select select2-modal-edit" multiple="multiple" style="width: 100%;">
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ in_array($role->id, old('roles', $userRoleIds)) ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- สิทธิ์ Admin และ Category Scope ใน Modal แก้ไข -->
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-secondary font-heading">ระดับสิทธิ์แอดมินหลัก <span class="text-danger">*</span></label>
                                <select name="is_admin" class="form-select shadow-none">
                                    <option value="0" {{ old('is_admin', $user->is_admin ? '1' : '0') == '0' ? 'selected' : '' }}>
                                        ผู้ใช้งานทั่วไป (General User)
                                    </option>
                                    <option value="1" {{ old('is_admin', $user->is_admin ? '1' : '0') == '1' ? 'selected' : '' }}>
                                        ผู้ดูแลระบบระดับสูง (System Admin)
                                    </option>
                                </select>
                            </div>

                            <!-- เลือกหมวดหมู่ที่ได้รับมอบหมาย -->
                            <div class="col-12">
                                <label class="form-label small fw-bold text-secondary font-heading">
                                    <i class="bi bi-folder-check text-warning me-1"></i> ขอบเขตหมวดหมู่ข่าวสาร/บทความที่อนุญาตให้จัดการ (Assigned Categories)
                                </label>
                                @php $userCatIds = $user->categories->pluck('id')->toArray(); @endphp
                                <select name="categories[]" class="form-select select2-modal-edit" multiple="multiple" style="width: 100%;">
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ in_array($cat->id, old('categories', $userCatIds)) ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">
                                    * หากผู้ใช้มีบทบาท Super Admin หรือสิทธิ์ <code>manage_all_categories</code> จะจัดการได้ทุกหมวดหมู่อัตโนมัติ
                                </small>
                            </div>

                            <!-- เปลี่ยนรหัสผ่าน -->
                            <div class="col-12">
                                <div class="bg-light p-3 rounded-3 border">
                                    <span class="d-block small fw-bold text-dark font-heading mb-2">
                                        <i class="bi bi-key text-warning me-1"></i> เปลี่ยนรหัสผ่านความปลอดภัย (ปล่อยว่างหากไม่ต้องการเปลี่ยน)
                                    </span>
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <input type="password" name="password" class="form-control form-control-sm" placeholder="รหัสผ่านใหม่ขั้นต่ำ 8 ตัวอักษร">
                                        </div>
                                        <div class="col-md-6">
                                            <input type="password" name="password_confirmation" class="form-control form-control-sm" placeholder="ยืนยันรหัสผ่านใหม่อีกครั้ง">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light py-2 px-4">
                        <button type="button" class="btn btn-secondary btn-sm px-3 font-heading" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary btn-sm px-4 shadow-sm font-heading fw-bold">บันทึกการแก้ไข</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endforeach

<!-- ➕ Modal สร้างบัญชีสมาชิกใหม่ -->
<div class="modal fade" id="createUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            <div class="modal-content border-top border-4 border-success rounded-4">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-dark fs-6 font-heading">
                        <i class="bi bi-person-plus-fill text-success me-1"></i> สร้างบัญชีผู้ใช้งานระบบรายใหม่
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4 font-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary font-heading">ชื่อ-นามสกุล บุคลากร <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control shadow-none" placeholder="ระบุชื่อจริงและนามสกุล" value="{{ old('name') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary font-heading">อีเมลกลางองค์กร <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control shadow-none" placeholder="example@tru.ac.th" value="{{ old('email') }}" required>
                        </div>

                        <!-- เลือกบทบาทหน้าที่ RBAC Roles -->
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary font-heading">
                                <i class="bi bi-shield-lock text-primary me-1"></i> มอบหมายบทบาทหน้าที่ (Roles)
                            </label>
                            <select name="roles[]" class="form-select select2-modal-create" multiple="multiple" style="width: 100%;">
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ is_array(old('roles')) && in_array($role->id, old('roles')) ? 'selected' : '' }}>
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- สิทธิ์ Admin -->
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary font-heading">ระดับสิทธิ์แอดมินหลัก</label>
                            <select name="is_admin" class="form-select shadow-none">
                                <option value="0" selected>ผู้ใช้งานทั่วไป (General User)</option>
                                <option value="1">ผู้ดูแลระบบระดับสูง (System Admin)</option>
                            </select>
                        </div>

                        <!-- เลือกหมวดหมู่ที่อนุญาตให้ดูแล -->
                        <div class="col-12">
                            <label class="form-label small fw-bold text-secondary font-heading">
                                <i class="bi bi-folder-check text-warning me-1"></i> ขอบเขตหมวดหมู่ข่าวสารที่อนุญาตให้ดูแล (Categories Scope)
                            </label>
                            <select name="categories[]" class="form-select select2-modal-create" multiple="multiple" style="width: 100%;">
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ is_array(old('categories')) && in_array($cat->id, old('categories')) ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- กำหนดรหัสผ่านเริ่มต้น -->
                        <div class="col-12">
                            <div class="p-3 bg-light rounded-3 border">
                                <label class="form-label small fw-bold text-dark font-heading mb-2">
                                    <i class="bi bi-key text-warning me-1"></i> กำหนดรหัสผ่านเริ่มต้น <span class="text-danger">*</span>
                                </label>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <input type="password" name="password" class="form-control form-control-sm" placeholder="รหัสผ่านอย่างน้อย 8 ตัวอักษร" required>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="password" name="password_confirmation" class="form-control form-control-sm" placeholder="ยืนยันรหัสผ่านอีกครั้ง" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2 px-4">
                    <button type="button" class="btn btn-secondary btn-sm px-3 font-heading" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-success btn-sm px-4 shadow-sm font-heading fw-bold">ยืนยันสร้างบัญชี</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('admin_scripts')
<script>
    $(document).ready(function() {
        // 1. เปิดใช้งาน Select2 บน Modal อย่างปลอดภัยเมื่อ Modal แสดงผล
        $('#createUserModal').on('shown.bs.modal', function () {
            $('.select2-modal-create').select2({
                dropdownParent: $('#createUserModal'),
                placeholder: "คลิกเพื่อเลือกรายการ..."
            });
        });

        $('.edit-user-modal').on('shown.bs.modal', function () {
            var currentModal = $(this);
            currentModal.find('.select2-modal-edit').select2({
                dropdownParent: currentModal,
                placeholder: "คลิกเพื่อเลือกรายการ..."
            });
        });

        // 2. SweetAlert2 สำหรับยืนยันการลบสมาชิก
        $('.btn-delete-trigger').click(function(e) {
            e.preventDefault();
            var currentForm = $(this).closest('form');

            Swal.fire({
                title: 'ยืนยันทำลายสิทธิ์ผู้ใช้?',
                text: "การลบบัญชีสมาชิกนี้จะส่งผลให้บุคลากรดังกล่าวไม่สามารถล็อกอินเข้าสู่ระบบได้อีกต่อไป!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#1E293B',
                confirmButtonText: '<i class="bi bi-trash me-1"></i> ยืนยันลบออกจากระบบ',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    currentForm.submit();
                }
            });
        });
    });
</script>
@endpush