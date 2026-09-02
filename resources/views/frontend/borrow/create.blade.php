@extends('layouts.frontend')

@section('seo')
    <title>ลงทะเบียนยืมอุปกรณ์และครุภัณฑ์ - สำนักศิลปะและวัฒนธรรม</title>
    <meta name="description" content="แบบฟอร์มลงทะเบียนยืมอุปกรณ์ประกอบการแสดง เครื่องแต่งกาย และครุภัณฑ์ สำนักศิลปะและวัฒนธรรม">
@endsection

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header text-center py-3" style="background: linear-gradient(135deg, var(--tru-purple-dark) 0%, #1E082A 100%);">
                    <h4 class="mb-0 text-white">ลงทะเบียนยืมอุปกรณ์และครุภัณฑ์</h4>
                </div>
                <div class="card-body p-4">
                    
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('borrow.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="borrower_name" class="form-label">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="borrower_name" name="borrower_name" value="{{ old('borrower_name') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="borrower_status" class="form-label">สถานะผู้ยืม <span class="text-danger">*</span></label>
                            <select class="form-select" id="borrower_status" name="borrower_status" required>
                                <option value="">เลือกสถานะ...</option>
                                <option value="นักศึกษา" {{ old('borrower_status') == 'นักศึกษา' ? 'selected' : '' }}>นักศึกษา</option>
                                <option value="บุคลากร" {{ old('borrower_status') == 'บุคลากร' ? 'selected' : '' }}>บุคลากร</option>
                                <option value="บุคคลภายนอก" {{ old('borrower_status') == 'บุคคลภายนอก' ? 'selected' : '' }}>บุคคลภายนอก</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="faculty_department" class="form-label">คณะ/หน่วยงาน <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="faculty_department" name="faculty_department" value="{{ old('faculty_department') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="phone_number" class="form-label">เบอร์โทรศัพท์ <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="phone_number" name="phone_number" value="{{ old('phone_number') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="equipment_name" class="form-label">รายการอุปกรณ์/ครุภัณฑ์ที่ยืม <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="equipment_name" name="equipment_name" value="{{ old('equipment_name') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="quantity" class="form-label">จำนวนที่ยืม <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="quantity" name="quantity" min="1" value="{{ old('quantity', 1) }}" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="borrow_date" class="form-label">วันที่ยืม <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="borrow_date" name="borrow_date" value="{{ old('borrow_date', date('Y-m-d')) }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="expected_return_date" class="form-label">วันที่กำหนดคืน <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="expected_return_date" name="expected_return_date" value="{{ old('expected_return_date') }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="purpose" class="form-label">วัตถุประสงค์ในการยืม (ทางเลือก)</label>
                            <textarea class="form-control" id="purpose" name="purpose" rows="2">{{ old('purpose') }}</textarea>
                        </div>

                        <hr class="my-4">

                        <div class="mb-4">
                            <label for="image" class="form-label fw-bold">แนบรูปถ่ายอุปกรณ์ (ทางเลือก)</label>
                            <p class="text-muted small mb-2">รองรับไฟล์ JPG, PNG สามารถถ่ายจากกล้องมือถือได้ทันที</p>
                            <!-- Use capture="environment" to trigger back camera on mobile devices -->
                            <input type="file" class="form-control form-control-lg" id="image" name="image" accept="image/jpeg, image/png" capture="environment">
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-warning text-dark btn-lg">ส่งข้อมูลการยืม</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection