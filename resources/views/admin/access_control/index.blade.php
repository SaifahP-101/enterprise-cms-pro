@extends('layouts.admin')

@section('title', 'ระบบตรวจสอบสิทธิ์และธุรกรรมย้อนหลัง')

@section('admin_content')
<div class="container-fluid animate__animated animate__fadeIn">
    <div class="mb-4">
        <h2 class="h4 mb-1 text-dark fw-bold"><i class="bi bi-shield-check"></i> ศูนย์ตรวจสอบสิทธิ์การใช้งานและรอยเท้าดิจิทัล (Audit Logs Control)</h2>
        <p class="text-muted small">ระบบสแกนตรวจสอบการทำงานของผู้ดูแลระบบหลังบ้าน ประมวลผลแบบ Server-side AJAX ความเร็วสูง</p>
    </div>

    <!-- กระดานบอร์ดแสดงข้อมูลหลัก -->
    <div class="card main-content-card border-0 shadow-sm overflow-hidden" style="border-top: 3px solid var(--theme-indigo) !important;">
        <div class="card-body p-4">
            <table class="table table-hover align-middle w-100 mb-0" id="serverSideAuditLogsTable" style="font-size: 0.9rem;">
                <thead class="bg-light text-secondary fw-bold">
                    <tr>
                        <th width="70">ID ล็อก</th>
                        <th width="160"> วันเวลาทำรายการ</th>
                        <th width="180"><i class="bi bi-person"></i> เจ้าหน้าที่รับผิดชอบ</th>
                        <th width="100">Action</th>
                        <th><i class="bi bi-folder"></i> โมดูล / แถวข้อมูลที่กระทำ</th>
                        <th width="130"><i class="bi bi-globe"></i> IP Address</th>
                        <th width="110" class="text-center">ดูเชิงลึก</th>
                    </tr>
                </thead>
                <tbody class="text-dark">
                    <!-- ข้อมูลแถวตารางจะถูกฉีดพ่นเข้ามาแบบอัตโนมัติผ่านท่อ AJAX ไร้สาย -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!--  MODAL WINDOW: หน้าต่างกางม่านโชว์เปรียบเทียบค่าความแตกต่างข้อมูล (JSON Data Diffing Showcase) -->
<div class="modal fade" id="payloadViewerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white border-bottom py-3 px-4" style="background-color: var(--theme-indigo);">
                <h5 class="modal-title fw-bold"><i class="bi bi-search"></i> เจาะลึกความสอดคล้องข้อมูล (Data Payload Breakdown)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 rounded border bg-light h-100">
                            <span class="d-block small fw-bold text-danger border-bottom pb-2 mb-2"> ค่าข้อมูลชุดเดิมในระบบ (Old Value State)</span>
                            <pre id="oldPayloadBox" class="p-2 bg-dark text-warning rounded small style-scrollbar" style="max-height: 280px; overflow-y:auto; font-family: monospace;"></pre>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded border bg-light h-100">
                            <span class="d-block small fw-bold text-success border-bottom pb-2 mb-2"><i class="bi bi-circle-fill text-success" style="font-size: 0.7rem;"></i> ค่าข้อมูลที่ถูกบันทึกใหม่ (New Value State)</span>
                            <pre id="newPayloadBox" class="p-2 bg-dark text-info rounded small style-scrollbar" style="max-height: 280px; overflow-y:auto; font-family: monospace;"></pre>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top py-2 px-4">
                <button type="button" class="btn btn-secondary fw-semibold px-4" data-bs-dismiss="modal">ปิดหน้าต่าง</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('admin_scripts')
<script>
    $(document).ready(function() {
        //  บูตระบบโครงสร้าง DataTables ให้ดึงข้อมูลสดข้ามเครือข่ายความเร็วสูงรูปแบบ Server-side processing
        var logsTable = $('#serverSideAuditLogsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.access-control.logs-data') }}",
            order: [[0, "desc"]], // ตั้งต้นให้แสดงไอดีล็อกล่าสุดขึ้นก่อนเสมอ
            columns: [
                { data: 'id', name: 'id', className: 'text-center font-weight-bold text-secondary' },
                { data: 'created_at', name: 'created_at' },
                { data: 'user', name: 'user' },
                { 
                    data: 'action', 
                    name: 'action',
                    className: 'text-center',
                    render: function(data) {
                        // เปลี่ยนสี Badge สลักแยกตามประเภทธุรกรรมข้อมูล
                        let badgeClass = 'bg-secondary';
                        if(data === 'CREATE') badgeClass = 'bg-success';
                        if(data === 'UPDATE') badgeClass = 'bg-warning text-dark';
                        if(data === 'DELETE') badgeClass = 'bg-danger';
                        return `<span class="badge ${badgeClass} px-2.5 py-1.5 rounded fw-bold">${data}</span>`;
                    }
                },
                { data: 'auditable_type', name: 'auditable_type', className: 'fw-semibold' },
                { data: 'ip_address', name: 'ip_address', className: 'text-muted' },
                { data: 'details', name: 'details', orderable: false, searchable: false, className: 'text-center' }
            ],
            language: {
                processing: "⏳ ระบบกำลังดึงประวัติธุรกรรมข้อมูล...",
                search: " ค้นหาล็อกด่วน:",
                lengthMenu: "แสดง _MENU_ แถวต่อหน้า",
                info: "แสดงประวัติลำดับที่ _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการล็อกระบบ",
                paginate: { first: "⏮️", previous: "◀️", next: "▶️", last: "⏭️" }
            }
        });

        //  ดักจับคลิกปุ่ม "ดู Payload" เพื่อนำข้อมูลข้อตกลง JSON มากระจายสวยงามในหน้าต่างมอดอล
        $(document).on('click', '.view-log-payload', function(e) {
            e.preventDefault();
            var rawOld = $(this).attr('data-old');
            var rawNew = $(this).attr('data-new');

            // ฟังก์ชันเคลียร์หน้าและจัดโครงสร้างสเปซย่อหน้าเพื่อการอ่านง่าย (Prettify JSON)
            function prettifyJson(rawString) {
                try {
                    let jsonObj = JSON.parse(rawString);
                    return JSON.stringify(jsonObj, null, 4);
                } catch(err) {
                    return rawString; // หากไม่ใช่ข้อความ JSON ให้ส่งข้อความดั้งเดิมกลับไป
                }
            }

            $('#oldPayloadBox').text(prettifyJson(rawOld));
            $('#newPayloadBox').text(prettifyJson(rawNew));

            // ปลุกสั่งงานเปิดโมดอลลับขึ้นแสดงหน้าร้าน
            $('#payloadViewerModal').modal('show');
        });
    });
</script>
@endpush