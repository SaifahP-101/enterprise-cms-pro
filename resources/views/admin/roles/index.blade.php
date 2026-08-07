@extends('layouts.admin')

@section('title', 'บริหารจัดการบทบาทและสิทธิ์การใช้งาน (RBAC)')

@push('admin_styles')
<style>
    /* 🎨 Card Styling & Hover Effects */
    .role-card {
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        border-top: 4px solid #4C1D95; /* TRU Purple */
    }
    .role-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 30px rgba(76, 29, 149, 0.12) !important;
    }

    /* 🏷️ Badge Permission Styling */
    .badge-permission {
        background-color: #F3E8FF; /* Light Lavender */
        color: #4C1D95; /* Deep Purple */
        border: 1px solid #E9D5FF;
        font-size: 0.75rem;
        padding: 5px 10px;
        font-weight: 500;
        white-space: nowrap; /* ป้องกันข้อความใน Badge ตัดบรรทัดน่าเกลียด */
    }

    /* 📏 Minimum Height Enforcer สำหรับคำอธิบายบทบาท */
    .min-h-40 {
        min-height: 45px;
        display: -webkit-box;
        -webkit-line-clamp: 2; /* บังคับให้แสดงแค่ 2 บรรทัด ถ้าเกินให้เป็น ... */
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* 📜 Custom Slim Scrollbar สำหรับกล่องแสดงรายการสิทธิ์ */
    .style-scrollbar::-webkit-scrollbar {
        width: 4px;
    }
    .style-scrollbar::-webkit-scrollbar-track {
        background: #F8FAFC;
        border-radius: 4px;
    }
    .style-scrollbar::-webkit-scrollbar-thumb {
        background: #CBD5E1;
        border-radius: 4px;
    }
    .style-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #94A3B8;
    }
</style>
@endpush

@section('admin_content')
<div class="container-fluid">
    
    <!-- 📌 Header Section -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="h4 mb-1 text-dark fw-bold font-heading">
                <i class="bi bi-shield-check text-primary me-2"></i>จัดการบทบาทและสิทธิ์เข้าถึงระบบ (RBAC)
            </h2>
            <p class="text-muted small mb-0 font-body">กำหนดระดับการเข้าถึงหน้างานและควบคุมคำสั่งสำคัญสำหรับผู้ดูแลระบบ มรภ.เทพสตรี</p>
        </div>
        <div>
            <a href="{{ route('admin.roles.create') }}" class="btn btn-sm btn-dark px-3 py-2 rounded-3 shadow-2xs fw-bold font-heading">
                <i class="bi bi-plus-circle me-1"></i> + สร้างบทบาทใหม่
            </a>
        </div>
    </div>

    <!-- 📌 Notification Alerts -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-2xs rounded-3 mb-4 font-body" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-2xs rounded-3 mb-4 font-body" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- 📌 Roles Grid Cards -->
    <div class="row g-4">
        @forelse($roles as $role)
            <div class="col-md-6 col-xl-4">
                <!-- ⚡ ห่อ Card ด้วย h-100 และ Flexbox Column เพื่อให้การ์ดสูงเท่ากันเสมอ -->
                <div class="card role-card h-100 border-0 shadow-sm rounded-4 p-4 bg-white d-flex flex-column">
                    
                    <!-- 📦 ส่วนเนื้อหาหลักของการ์ด (ใช้ flex-grow-1 เพื่อดัน Footer ลงไปขอบล่างสุด) -->
                    <div class="flex-grow-1">
                        
                        <!-- Header Card -->
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="fw-bold text-dark mb-1 font-heading">{{ $role->name }}</h5>
                                <span class="badge bg-light text-secondary border font-monospace small px-2 py-1">{{ $role->slug }}</span>
                            </div>
                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2 fw-semibold small">
                                <i class="bi bi-people-fill me-1"></i> {{ number_format($role->users_count) }} ผู้ใช้
                            </span>
                        </div>

                        <!-- Description -->
                        <p class="text-muted small mb-3 min-h-40 font-body">
                            {{ $role->description ?? 'ไม่มีคำอธิบายบทบาทการใช้งาน' }}
                        </p>

                        <!-- Attached Permissions Preview -->
                        <div class="mb-4">
                            <span class="d-block small text-secondary fw-bold mb-2 font-heading">
                                <i class="bi bi-key-fill text-warning me-1"></i> สิทธิ์ที่ได้รับการอนุมัติ ({{ $role->permissions->count() }} รายการ):
                            </span>
                            <!-- กล่องรายการสิทธิ์ ซ่อน scrollbar อัตโนมัติเมื่อเนื้อหาน้อยกว่า 100px -->
                            <div class="d-flex flex-wrap gap-1.5 style-scrollbar pe-1" style="max-height: 105px; overflow-y: auto;">
                                @forelse($role->permissions->take(8) as $perm)
                                    <span class="badge badge-permission rounded-2">{{ $perm->name }}</span>
                                @empty
                                    <span class="text-muted small italic font-body">ยังไม่ได้กำหนดสิทธิ์ในบทบาทนี้</span>
                                @endforelse

                                @if($role->permissions->count() > 8)
                                    <span class="badge bg-secondary text-white rounded-2 px-2 py-1 d-flex align-items-center">
                                        +{{ $role->permissions->count() - 8 }} เพิ่มเติม
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- 📦 Footer Card Actions (จะอยู่ติดขอบล่างเสมอเพราะ flex-grow-1 ดันลงมา) -->
                    <div class="border-top pt-3 d-flex justify-content-between align-items-center mt-auto">
                        <small class="text-muted font-body" style="font-size: 0.75rem;">
                            แก้ไขล่าสุด: {{ $role->updated_at->format('d/m/Y H:i') }}
                        </small>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold font-heading">
                                <i class="bi bi-pencil-square me-1"></i> แก้ไข
                            </a>
                            
                            @if($role->slug !== 'super_admin')
                                <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" class="d-inline form-delete-role">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 shadow-none" title="ลบบทบาทนี้">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                    
                </div>
            </div>
        @empty
            <!-- Fallback Empty State -->
            <div class="col-12 text-center py-5">
                <i class="bi bi-shield-slash text-muted opacity-25" style="font-size: 4rem;"></i>
                <p class="text-muted mt-2 mb-0 font-body fs-5">ยังไม่มีการสร้างบทบาทและกำหนดสิทธิ์ผู้ใช้ในระบบ</p>
                <a href="{{ route('admin.roles.create') }}" class="btn btn-outline-primary mt-3 rounded-pill px-4 font-heading">สร้างบทบาทแรกตอนนี้เลย</a>
            </div>
        @endforelse
    </div>

</div>
@endsection

@push('admin_scripts')
<script>
    $(document).ready(function() {
        // 🛑 SweetAlert2 สำหรับยืนยันการลบแบบปลอดภัย
        $('.form-delete-role').submit(function(e) {
            e.preventDefault();
            var form = this;
            Swal.fire({
                title: 'ยืนยันทำลายบทบาทนี้?',
                text: "ผู้ใช้ที่ผูกติดกับบทบาทนี้จะถูกยกเลิกสิทธิ์การทำงานทันที คุณไม่สามารถกู้คืนได้!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#1E293B',
                confirmButtonText: '<i class="bi bi-trash me-1"></i> ลบบทบาทถาวร',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endpush