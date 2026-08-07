@extends('layouts.admin')

@section('title', 'ระบบบริหารจัดการไฟล์สำรองข้อมูล (Offsite Backup)')

@section('admin_content')
<div class="container-fluid py-4">
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h4 class="fw-bold text-dark mb-1">
                    <i class="bi bi-cloud-arrow-up-fill text-primary me-2"></i>ระบบ Offsite Backup (Google Drive OAuth 2.0)
                </h4>
                <p class="text-muted small mb-0">
                    สำรองข้อมูล Database (.sql.gz) และไฟล์สื่อใน storage/app/public แบบ Smart Incremental Sync
                </p>
            </div>
            
            <button type="button" id="btnTriggerBackup" class="btn btn-primary px-4 py-2 rounded-3 fw-bold shadow-sm">
                <i class="bi bi-play-circle-fill me-2"></i>สั่งสำรองข้อมูลทันที (Backup Now)
            </button>
        </div>
    </div>

    <!-- ตาราง Audit Logs -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-transparent border-0 p-4 pb-0">
            <h6 class="fw-bold text-dark mb-0">
                <i class="bi bi-clock-history me-2 text-secondary"></i>ประวัติการดำเนินการสำรองข้อมูลล่าสุด
            </h6>
        </div>
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>วัน-เวลา</th>
                            <th>ประเภท</th>
                            <th>สถานะ</th>
                            <th>รายละเอียดกระบวนการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($backupLogs as $log)
                            @php
                                $payload = json_decode($log->new_values, true);
                                $isSuccess = str_contains($log->action, 'SUCCESS');
                            @endphp
                            <tr>
                                <td class="small font-monospace">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                <td>
                                    <span class="badge {{ ($payload['trigger_type'] ?? '') === 'MANUAL' ? 'bg-info text-dark' : 'bg-secondary' }} px-2 py-1">
                                        {{ $payload['trigger_type'] ?? 'CRON' }}
                                    </span>
                                </td>
                                <td>
                                    @if($isSuccess)
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">
                                            <i class="bi bi-check-circle-fill me-1"></i>สำเร็จ
                                        </span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1">
                                            <i class="bi bi-x-circle-fill me-1"></i>ล้มเหลว
                                        </span>
                                    @endif
                                </td>
                                <td class="small text-secondary">{{ $payload['details'] ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">ยังไม่มีประวัติการสำรองข้อมูลในระบบ</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $backupLogs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('admin_scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const btnBackup = document.getElementById('btnTriggerBackup');

        if (btnBackup) {
            btnBackup.addEventListener('click', function (e) {
                e.preventDefault();

                Swal.fire({
                    title: 'ยืนยันการสำรองข้อมูล?',
                    text: "ระบบจะส่ง Job เข้าสู่ Background Queue เพื่ออัปโหลดไฟล์ขึ้น Google Drive",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#0D6EFD',
                    cancelButtonColor: '#6C757D',
                    confirmButtonText: 'ยืนยัน สั่งรันทันที',
                    cancelButtonText: 'ยกเลิก'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'กำลังส่งคำสั่ง...',
                            text: 'โปรดรอสักครู่',
                            allowOutsideClick: false,
                            didOpen: () => { Swal.showLoading(); }
                        });

                        fetch("{{ route('admin.settings.backup.run') }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'ส่งคำสั่งสำเร็จ!',
                                    text: data.message,
                                    confirmButtonColor: '#0D6EFD'
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                throw new Error(data.message || 'เกิดข้อผิดพลาด');
                            }
                        })
                        .catch(error => {
                            Swal.fire({
                                icon: 'error',
                                title: 'เกิดข้อผิดพลาด',
                                text: error.message,
                                confirmButtonColor: '#DC3545'
                            });
                        });
                    }
                });
            });
        }
    });
</script>
@endpush