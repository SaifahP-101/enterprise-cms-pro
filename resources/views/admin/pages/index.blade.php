@extends('layouts.admin')

@section('title', 'จัดการหน้าเพจอิสระ')

@section('admin_content')
<div class="container-fluid animate__animated animate__fadeIn">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-1 text-dark fw-bold"><i class="bi bi-laptop"></i> คลังหน้าเพจโครงสร้างอิสระ (Static Pages Base)</h2>
            <p class="text-muted small mb-0">สร้างและคัดลอกลิงก์หน้าเพจไปผูกเข้าหาปุ่ม Banner หรือแถบเมนูหน้าเว็บได้อย่างอิสระ</p>
        </div>
        <a href="{{ route('admin.pages.create') }}" class="btn btn-primary shadow-sm fw-bold px-4">
            + สร้างหน้าเพจใหม่
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm" style="border-left: 4px solid #198754;">
            {{ session('success') }}
        </div>
    @endif

    <div class="card main-content-card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive style-scrollbar">
            <table class="table table-hover align-middle mb-0 text-nowrap">
                <thead class="bg-light text-secondary fw-bold" style="font-size: 0.85rem;">
                    <tr>
                        <th width="60" class="text-center py-3 ps-4">ID</th>
                        <th>ชื่อหน้าเพจ (Title)</th>
                        <th>คีย์เส้นทางด่วน (Slug Path)</th>
                        <th width="100" class="text-center">ยอดวิวยอดชม</th>
                        <th width="120" class="text-center">สถานะ</th>
                        <th width="260" class="text-center py-3 pe-4">ปฏิบัติการระบบ</th>
                    </tr>
                </thead>
                <tbody style="font-size: 0.9rem;">
                    @forelse($pages as $page)
                    <tr>
                        <td class="text-center fw-bold text-secondary ps-4">{{ $page->id }}</td>
                        <td><strong class="text-dark">{{ $page->title }}</strong></td>
                        <td><code class="text-primary">/page/{{ $page->slug }}</code></td>
                        <td class="text-center fw-medium">{{ number_format($page->view_count) }}</td>
                        <td class="text-center">
                            {!! $page->is_active ? '<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-1.5 fw-bold"><i class="bi bi-circle-fill text-success" style="font-size: 0.7rem;"></i> ออนไลน์</span>' : '<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-3 py-1.5 fw-bold"><i class="bi bi-circle-fill text-secondary" style="font-size: 0.7rem;"></i> ปิดซ่อน</span>' !!}
                        </td>
                        <td class="text-center pe-4">
                            <!--  ENTERPRISE UX FEATURE: แท่นกดปุ่มคัดลอกลิงก์ส่งต่อไปใช้งานภายนอกทันที -->
                            <button class="btn btn-sm btn-dark fw-bold px-3 btn-copy-link" data-url="{{ route('pages.show', $page->slug) }}">
                                <i class="bi bi-clipboard"></i> คัดลอกลิงก์
                            </button>
                            <a href="{{ route('admin.pages.edit', $page->id) }}" class="btn btn-sm btn-outline-warning fw-bold px-3"><i class="bi bi-pencil-square"></i> แก้ไข</a>
                            <form action="{{ route('admin.pages.destroy', $page->id) }}" method="POST" class="d-inline form-delete-action">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger fw-bold px-3"><i class="bi bi-x-lg"></i> ลบ</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-5 text-muted">ยังไม่มีหน้าเพจอิสระถูกจัดสร้างไว้ในระบบคลัง</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('admin_scripts')
<script>
    $(document).ready(function() {
        //  ลอจิกไร้สายสำหรับกวาดจับคัดลอกข้อความ URL ไปยัง Clipboard เครื่องผู้ใช้ทันที
        $('.btn-copy-link').click(function(e) {
            e.preventDefault();
            var targetUrl = $(this).data('url');
            
            navigator.clipboard.writeText(targetUrl).then(function() {
                Swal.fire({
                    icon: 'success',
                    title: 'คัดลอกลิงก์เพจสำเร็จ!',
                    text: 'นำ URL นี้ไปใช้วางบนเมนูหรือแบนเนอร์ได้ทันที',
                    timer: 1500,
                    showConfirmButton: false
                });
            });
        });

        $('.form-delete-action').on('submit', function(e) {
            e.preventDefault();
            var handlingForm = this;
            Swal.fire({
                title: 'ยืนยันการลบหน้าเพจนี้?',
                text: "หน้าเพจจะถูกย้ายไปถังขยะสำรอง สามารถกู้คืนได้ภายหลัง",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#202040',
                confirmButtonText: 'ลบลงถังขยะ'
            }).then((result) => { if (result.isConfirmed) { handlingForm.submit(); } });
        });
    });
</script>
@endpush