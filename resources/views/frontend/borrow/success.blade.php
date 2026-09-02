@extends('layouts.frontend')

@section('content')
<div class="container py-5 text-center">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <!-- Icon Checkmark from Bootstrap Icons (if available in your CMS) -->
            <i class="bi bi-check-circle text-success" style="font-size: 5rem;"></i>
            <h2 class="mt-4 fw-bold">ลงทะเบียนเรียบร้อยแล้ว</h2>
            <p class="text-muted mt-3">
                ระบบได้บันทึกข้อมูลการยืมอุปกรณ์ของคุณเรียบร้อยแล้ว<br>
                ขอบคุณที่ใช้บริการสำนักศิลปะและวัฒนธรรมครับ
            </p>
            <a href="{{ url('/') }}" class="btn btn-outline-secondary mt-4">กลับสู่หน้าหลัก</a>
        </div>
    </div>
</div>
@endsection