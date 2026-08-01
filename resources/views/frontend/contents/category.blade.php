@extends('layouts.frontend')

<!--  Advanced SEO & Open Graph For Dynamic Categories -->
@section('seo')
    <title>หมวดหมู่: {{ $category->name }} | สำนักศิลปะและวัฒนธรรม มรภ.เทพสตรี</title>
    <meta name="description" content="รวบรวมคลังข้อมูลวิชาการ ข่าวสาร และกิจกรรมทางวัฒนธรรมในหมวดหมู่ {{ $category->name }} มหาวิทยาลัยราชภัฏเทพสตรี ลพบุรี">
    <meta property="og:type" content="website">
    <meta property="og:title" content="หมวดหมู่: {{ $category->name }} | สำนักศิลปะและวัฒนธรรม">
    <meta property="og:description" content="เข้าชมคลังสารสนเทศภูมิปัญญาแยกตามหมวดหมู่ {{ $category->name }} ระบบสืบค้นความเร็วสูง">
    <meta property="og:url" content="{{ url()->current() }}">
@endsection

@section('content')
<div class="container animate__animated animate__fadeIn">
    <!-- Breadcrumb & Header Title -->
    <div class="mb-5 border-bottom pb-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">หน้าแรก</a></li>
                <li class="breadcrumb-item active text-dark fw-bold" aria-current="page">หมวดหมู่เนื้อหา</li>
            </ol>
        </nav>
        <h2 class="fw-bold text-dark d-flex align-items-center gap-2">
            <span style="color: var(--cms-gold);"><i class="bi bi-folder"></i></span> สารสนเทศในหมวดหมู่: <span class="text-primary">{{ $category->name }}</span>
        </h2>
        <p class="text-muted small mb-0 mt-1">พบข้อมูลข่าวสารและกิจกรรมภูมิปัญญาท้องถิ่นทั้งหมดจำนวน <strong>{{ $contents->total() }}</strong> รายการ</p>
    </div>

    <!-- Content Grid Layout -->
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        @forelse($contents as $item)
            <div class="col">
                <div class="card h-100 border-0 shadow-sm overflow-hidden bg-white" style="border-top: 3.5px solid var(--cms-indigo) !important;">
                    <div style="height: 180px; overflow: hidden; background-color: #f8f9fa;">
                        @if($item->cover_image)
                            <img src="{{ asset('storage/' . $item->cover_image) }}" class="w-100 h-100" style="object-fit: cover;">
                        @else
                            <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted small bg-secondary bg-opacity-10"><i class="bi bi-file-earmark-text"></i> ไม่มีภาพปกประกอบ</div>
                        @endif
                    </div>
                    
                    <div class="card-body p-4 d-flex flex-column">
                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-10 align-self-start mb-2 px-2.5 py-1.5 fw-bold" style="font-size:0.75rem;">
                            <i class="bi bi-folder"></i> {{ $category->name }}
                        </span>
                        <h5 class="card-title fw-bold text-dark fs-6 lh-base mb-2 text-line-clamp-2" style="min-height: 44px;">
                            {{ $item->title }}
                        </h5>
                        <p class="card-text text-muted small lh-relaxed mb-4 text-line-clamp-3">
                            {{ Str::limit(strip_tags($item->body), 100) }}
                        </p>
                        <a href="{{ route('contents.show', $item->slug) }}" class="btn btn-sm btn-outline-dark fw-bold mt-auto w-100 py-2">
                             เปิดอ่านเนื้อหาฉบับเต็ม →
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 w-100 text-center py-5 bg-white rounded border shadow-sm">
                <div class="py-4 text-muted">
                    <span class="fs-1 d-block mb-2"></span>
                    <span class="fw-medium">ยังไม่มีข้อมูลสารสนเทศหรือกิจกรรมจัดอยู่ในหมวดหมู่ชิ้นนี้ในขณะนี้</span>
                </div>
            </div>
        @endforelse
    </div>

    <!--  Bootstrap 5 Pagination Workspace -->
    <div class="d-flex justify-content-center mt-5">
        {!! $contents->links('pagination::bootstrap-4') !!}
    </div>
</div>
@endsection