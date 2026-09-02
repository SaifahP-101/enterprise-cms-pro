@extends('layouts.admin')

@section('title', 'ระบบบริหารจัดการไฟล์สำรองข้อมูล (Offsite Backup)')

@section('admin_content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h4 class="fw-bold text-dark mb-1">
                    <i class="bi bi-cloud-arrow-up-fill text-primary me-2"></i>ระบบ Offsite Backup (Google Drive OAuth 2.0)
                </h4>
                <p class="text-muted small mb-0">
                    สำรองข้อมูล Database (.sql) และไฟล์สื่อใน storage/app/public แบบ Smart Incremental Sync
                </p>
            </div>
            
            @if(isset($isConnected) && !$isConnected)
                <div class="alert alert-warning mb-0 px-3 py-2 border-0 shadow-sm">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>กรุณาเชื่อมต่อบัญชี Google Drive ก่อนสั่งการ
                </div>
            @else
                <button type="button" id="btnTriggerBackup" class="btn btn-primary px-4 py-2 rounded-3 fw-bold shadow-sm">
                    <i class="bi bi-play-circle-fill me-2"></i>สั่งสำรองข้อมูลทันที (Backup Now)
                </button>
            @endif
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
                        @forelse($backupLogs ?? [] as $log)
                            @php
                                // ⚡ ตรวจสอบว่าเป็น Array แล้วหรือยัง (ผลจาก Model $casts)
                                $payload = is_array($log->new_values) ? $log->new_values : json_decode($log->new_values ?? '{}', true);
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

            <!-- Pagination -->
            <div class="mt-4">
                {{ $backupLogs->links() ?? '' }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('admin_scripts')
<!-- SweetAlert2 สำหรับแจ้งเตือนแบบ UI หรูหรา -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        
        // ⚡ 1. ฟังก์ชันติดตามสถานะแบบ Real-time (AJAX Polling)
        function startStatusPolling() {
            Swal.fire({
                title: 'กำลังดำเนินการ...',
                html: '<div id="backup-status-text" class="mt-2">กำลังเชื่อมต่อกับระบบหลังบ้าน...</div>',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => { Swal.showLoading(); }
            });

            // ส่ง Request ไปถาม Server ทุกๆ 2.5 วินาที
            const polling = setInterval(() => {
                fetch("{{ route('admin.settings.backup.status') }}")
                .then(res => res.json())
                .then(state => {
                    if (state.is_running) {
                        // อัปเดตข้อความใน SweetAlert สดๆ
                        document.getElementById('backup-status-text').innerHTML = 
                            `<b class="text-primary">${state.message}</b><br><small class="text-muted mt-2 d-block" style="line-height:1.5;">${state.details}</small>`;
                    } else {
                        // เมื่องานจบ (is_running = false) หรือ ไม่ได้ทำงานอยู่
                        clearInterval(polling);
                        
                        if (state.status === 'SUCCESS') {
                            Swal.fire('สำเร็จ!', state.details, 'success').then(() => window.location.reload());
                        } else if (state.status === 'FAILED') {
                            Swal.fire('ล้มเหลว!', state.details, 'error').then(() => window.location.reload());
                        } else {
                            // กรณีเช็กสถานะผิดพลาด หรือสถานะ IDLE
                            Swal.close();
                        }
                    }
                })
                .catch(err => console.log('Polling Connection Timeout/Error:', err));
            }, 2500);
        }

        // ⚡ 2. ป้องกัน F5: เช็กสถานะทันทีเมื่อโหลดหน้าเว็บ ถ้ามีงานค้างให้เด้ง Popup ทันที
        fetch("{{ route('admin.settings.backup.status') }}")
            .then(res => res.json())
            .then(state => {
                if (state.is_running) {
                    startStatusPolling(); 
                }
            });

        // ⚡ 3. จัดการเมื่อกดปุ่ม "สั่งสำรองข้อมูลทันที"
        const btnBackup = document.getElementById('btnTriggerBackup');
        if (btnBackup) {
            btnBackup.addEventListener('click', function (e) {
                e.preventDefault();

                Swal.fire({
                    title: 'ยืนยันการสำรองข้อมูล?',
                    text: "ระบบจะส่งงานเข้าสู่ Background Queue เพื่อทำ Smart Sync ไปยัง Google Drive",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#0D6EFD',
                    cancelButtonColor: '#6C757D',
                    confirmButtonText: 'ยืนยัน สั่งรันทันที',
                    cancelButtonText: 'ยกเลิก'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // โชว์หน้าโหลดดิ้งก่อนระหว่างยิงคำสั่ง
                        Swal.fire({ 
                            title: 'กำลังส่งคำสั่งเข้าระบบ...', 
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
                        .then(res => res.json())
                        .then(data => {
                            // รองรับทั้ง Response กลับมาเป็น success: true หรือ status: 'success'
                            if (data.success || data.status === 'success') {
                                // ส่งคำสั่งผ่านแล้ว เริ่มดักจับสถานะจาก Cache ได้เลย!
                                startStatusPolling();
                            } else {
                                Swal.fire('เกิดข้อผิดพลาด', data.message || 'ไม่สามารถส่งคำสั่งได้', 'error');
                            }
                        })
                        .catch(error => {
                            Swal.fire('ข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้ (Network Error)', 'error');
                        });
                    }
                });
            });
        }
    });
</script>
@endpush