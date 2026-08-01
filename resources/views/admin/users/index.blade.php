@extends('layouts.admin')

@section('title', 'จัดการข้อมูลสมาชิก')

@section('admin_content')
<div class="container-fluid">
    <!-- ส่วนหัวของโมดูลนวัตกรรม -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-1 text-dark fw-bold">ระบบบริหารจัดการสมาชิก (User Registry Management)</h2>
            <p class="text-muted small mb-0">เพิ่ม แก้ไข และควบคุมสิทธิ์การเข้าถึงระบบของบุคลากรภายในสำนักศิลปะและวัฒนธรรม</p>
        </div>
        <button class="btn btn-sm btn-success px-4 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#createUserModal">
            <i class="bi bi-sparkles"></i> เพิ่มบัญชีสมาชิกใหม่
        </button>
    </div>

    <!-- แผงตารางประมวลผลข้อมูลสมาชิก (DataTables Integration) -->
    <div class="card main-content-card shadow-sm border-0">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table id="usersEnterpriseTable" class="table table-striped align-middle" style="width:100%">
                    <thead class="table-light text-secondary small text-uppercase">
                        <tr>
                            <th style="width: 70px;">ID</th>
                            <th>ชื่อ-นามสกุล บุคลากร</th>
                            <th>อีเมลระบบ</th>
                            <th>ระดับสิทธิ์การเข้าถึง</th>
                            <th>วันที่ลงทะเบียน</th>
                            <th class="text-end" style="width: 160px;">การจัดการสิทธิ์</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td class="fw-bold text-secondary">#{{ $user->id }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle me-2 bg-dark text-white d-flex align-items-center justify-content-center fw-bold rounded-circle" style="width: 35px; height: 35px; font-size: 0.85rem;">
                                            {{ mb_substr($user->name, 0, 1, 'UTF-8') }}
                                        </div>
                                        <span class="fw-semibold text-dark">{{ $user->name }}</span>
                                    </div>
                                </td>
                                <td><code class="text-dark">{{ $user->email }}</code></td>
                                <td>
                                    @if($user->is_admin)
                                        <span class="badge bg-danger px-2.5 py-1.5 border-start border-3 border-warning rounded-1 shadow-xs">
                                            <i class="bi bi-building-columns"></i> ผู้ดูแลระบบ (Admin)
                                        </span>
                                    @else
                                        <span class="badge bg-primary px-2.5 py-1.5 rounded-1">
                                            <i class="bi bi-people"></i> ผู้ใช้งานทั่วไป (User)
                                        </span>
                                    @endif
                                </td>
                                <td><small class="text-muted">{{ $user->created_at->format('d/m/Y H:i') }} น.</small></td>
                                <td class="text-end">
                                    <!-- ปุ่มแก้ไขดึง Modal เจาะจง ID -->
                                    <button class="btn btn-xs btn-outline-primary me-1 px-2 py-1" data-bs-toggle="modal" data-bs-target="#editUserModal{{ $user->id }}">
                                        แก้ไข
                                    </button>
                                    <!-- ฟอร์มลบผูกกับสคริปต์ความปลอดภัย SweetAlert2 -->
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline delete-user-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-xs btn-outline-danger px-2 py-1 btn-delete-trigger">
                                            ลบข้อมูล
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            <!--  Modal สำหรับแก้ไขข้อมูลสมาชิกรายบุคคล -->
                            <div class="modal fade" id="editUserModal{{ $user->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-content border-top border-4 border-primary">
                                            <div class="modal-header bg-light">
                                                <h5 class="modal-title fw-bold text-dark fs-6"><i class="bi bi-pencil-square"></i> แก้ไขข้อมูลและสิทธิ์บุคลากร</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold text-secondary">ชื่อ-นามสกุล</label>
                                                    <input type="text" name="name" class="form-control form-control-sm shadow-xs" value="{{ $user->name }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold text-secondary">อีเมลเข้าใช้งาน</label>
                                                    <input type="email" name="email" class="form-control form-control-sm shadow-xs" value="{{ $user->email }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold text-secondary">สิทธิ์ในระบบ (Select2 Enhanced)</label>
                                                    <select name="is_admin" class="form-select select2-role-edit" style="width: 100%">
                                                        <option value="0" {{ !$user->is_admin ? 'selected' : '' }}><i class="bi bi-people"></i> ผู้ใช้งานทั่วไป (General User)</option>
                                                        <option value="1" {{ $user->is_admin ? 'selected' : '' }}><i class="bi bi-building-columns"></i> ผู้ดูแลระบบระดับสูง (System Administrator)</option>
                                                    </select>
                                                </div>
                                                <div class="bg-light p-3 rounded mb-2 border">
                                                    <span class="d-block small fw-bold text-warning mb-2"> เปลี่ยนรหัสผ่านความปลอดภัย (ปล่อยว่างหากไม่ต้องการเปลี่ยน)</span>
                                                    <div class="mb-2">
                                                        <input type="password" name="password" class="form-control form-control-sm" placeholder="รหัสผ่านใหม่ขั้นต่ำ 8 ตัวอักษร">
                                                    </div>
                                                    <div>
                                                        <input type="password" name="password_confirmation" class="form-control form-control-sm" placeholder="ยืนยันรหัสผ่านใหม่อีกครั้ง">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer bg-light py-2">
                                                <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">ยกเลิก</button>
                                                <button type="submit" class="btn btn-primary btn-sm px-4 shadow-sm">บันทึกการแก้ไข</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!--  Modal สำหรับลงทะเบียนสมาชิกใหม่ -->
<div class="modal fade" id="createUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            <div class="modal-content border-top border-4 border-success">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-dark fs-6"><i class="bi bi-sparkles"></i> สร้างบัญชีผู้ใช้งานระบบรายใหม่</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">ชื่อ-นามสกุล บุคลากร</label>
                        <input type="text" name="name" class="form-control form-control-sm" placeholder="ระบุชื่อจริงและนามสกุล" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">อีเมลกลางองค์กร</label>
                        <input type="email" name="email" class="form-control form-control-sm" placeholder="example@tru.ac.th" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">กำหนดระดับสิทธิ์ระบบ (Select2 Enhanced)</label>
                        <select name="is_admin" class="form-select select2-role-create" style="width: 100%">
                            <option value="0" selected><i class="bi bi-people"></i> ผู้ใช้งานทั่วไป (General User)</option>
                            <option value="1"><i class="bi bi-building-columns"></i> ผู้ดูแลระบบระดับสูง (System Administrator)</option>
                        </select>
                    </div>
                    <div class="row g-2 p-3 bg-light rounded border mx-0">
                        <label class="form-label small fw-bold text-dark px-0 mb-1"> รหัสผ่านเริ่มต้นสิทธิ์</label>
                        <div class="col-6 ps-0">
                            <input type="password" name="password" class="form-control form-control-sm" placeholder="รหัสผ่านอย่างน้อย 8 ตัว" required>
                        </div>
                        <div class="col-6 pe-0">
                            <input type="password" name="password_confirmation" class="form-control form-control-sm" placeholder="ยืนยันรหัสผ่านอีกครั้ง" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">ปิดหน้าต่าง</button>
                    <button type="submit" class="btn btn-success btn-sm px-4 shadow-sm">ยืนยันสร้างบัญชี</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('admin_scripts')
<script>
    $(document).ready(function() {
        // 1.  เปิดใช้งานระบบตารางแบบโต้ตอบความเร็วสูง DataTables (Local Setup)
        $('#usersEnterpriseTable').DataTable({
            responsive: true,
            pageLength: 10,
            ordering: true,
            language: {
                search: " ค้นหาสมาชิกด่วน:",
                lengthMenu: "แสดง _MENU_ เรคคอร์ดต่อหน้า",
                info: "แสดงลำดับที่ _START_ ถึง _END_ จากทั้งหมด _TOTAL_ บัญชี",
                infoEmpty: "ไม่มีข้อมูลแสดงผล",
                zeroRecords: " ไม่พบบัญชีสมาชิกที่ระบุในคลังระบบ",
                paginate: {
                    previous: "ก่อนหน้า",
                    next: "ถัดไป"
                }
            }
        });

        // 2.  เคลือบประสิทธิภาพ Select2 บนกล่องเลือกสิทธิ์ใน Modal ทั้งสองชุด
        $('.select2-role-create, .select2-role-edit').select2({
            dropdownParent: $('#createUserModal, .modal'), // แก้ปัญหากล่อง Select2 จมลึกลงใต้ชั้น Modal Layer
            minimumResultsForSearch: Infinity // ปิดช่องค้นหาเนื่องจากมีตัวเลือกสิทธิ์น้อย ป้องกัน UI ล้น
        });

        // 3.  เปลี่ยนระบบยืนยันการลบสมาชิกให้เป็น SweetAlert2 หรูหราตามอัตลักษณ์สีทองคำ (#DAA520)
        $('.btn-delete-trigger').click(function(e) {
            e.preventDefault();
            var currentForm = $(this).closest('form');

            Swal.fire({
                title: 'ยืนยันการทำลายสิทธิ์ผู้ใช้?',
                text: "การลบบัญชีสมาชิกนี้จะส่งผลให้บุคลากรดังกล่าวไม่สามารถล็อกอินเข้าสู่ระบบหลังบ้านได้อีกต่อไป!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#DAA520', // Gilded Gold Accent
                cancelButtonColor: '#202040',  // Dark Indigo Core
                confirmButtonText: ' ยืนยันลบออกจากระบบ',
                cancelButtonText: 'ยกเลิกกระบวนการ'
            }).then((result) => {
                if (result.isConfirmed) {
                    currentForm.submit(); // ส่งคำสั่งจริงเข้าสู่คลาสทำลายในชั้น Controller
                }
            });
        });

        // 4. ดักจับ Flash Messages จากระบบหลังบ้านเพื่อทำ Notification ป๊อปอัปสวยงาม
        @if(session('success'))
            Swal.fire({
                title: 'บันทึกข้อมูลสำเร็จ!',
                text: "{{ session('success') }}",
                icon: 'success',
                confirmButtonColor: '#DAA520'
            });
        @endif

        @if(session('error'))
            Swal.fire({
                title: 'ตรวจพบข้อผิดพลาด!',
                text: "{{ session('error') }}",
                icon: 'error',
                confirmButtonColor: '#202040'
            });
        @endif
    });
</script>
@endpush