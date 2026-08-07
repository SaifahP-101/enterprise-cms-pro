@extends('layouts.admin')

@section('title', 'แผงควบคุมระบบและวิเคราะห์สถิติข้อมูลจำเพาะ')

@section('admin_content')
<div class="container-fluid animate__animated animate__fadeIn">

    <!-- 1. ส่วนหัวรายการหลักประจำโมดูลหน้าแดชบอร์ด -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-1 text-dark fw-bold" style="font-family: 'Kanit', sans-serif;">
                <i class="bi bi-speedometer2 text-primary me-2"></i>ระบบวิเคราะห์และติดตามผลสัมฤทธิ์สารสนเทศ
            </h2>
            <p class="text-muted small mb-0">มอนิเตอร์ดัชนีชี้วัดทราฟฟิก ยอดการส่งต่อโซเชียล ยอดดาวน์โหลดเอกสาร และประสิทธิภาพรายบทความแยกตัวกรองอิสระ</p>
        </div>
    </div>

    <!-- 2. ส่วนควบคุมป้ายแบนเนอร์ต้อนรับอัตลักษณ์สถาบัน -->
    <div class="card border-0 mb-4 overflow-hidden shadow-sm rounded-4 position-relative" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
        <div class="position-absolute top-0 end-0 bottom-0 start-0" 
            style="background-image: linear-gradient(135deg, rgb(76 68 27 / 85%) 0%, rgba(30, 41, 59, 0.4) 100%), url({{ asset('assets/images/culture-tru.jpg') }}); background-position: center; background-size: cover; background-repeat: no-repeat;">
        </div>
        <div class="card-body p-5 position-relative z-1">
            <div class="row align-items-center">
                <div class="col-lg-9">
                    <h3 class="fw-bold text-warning mb-2" style="font-family: 'Kanit', sans-serif;">
                        <i class="bi bi-building-columns text-warning me-2"></i>สำนักศิลปะและวัฒนธรรม มหาวิทยาลัยราชภัฏเทพสตรี ลพบุรี
                    </h3>
                    <p class="mb-0 text-white-50" style="font-size: 0.95rem; line-height: 1.6;">
                        ยกระดับการวิเคราะห์ข้อมูลด้วย <strong>แผงควบคุมสถิติอัจฉริยะ (Smart Analytics Dashboard)</strong> ที่ผสานระบบตัวกรองขั้นสูงแยกอิสระในแต่ละส่วนประกอบ ช่วยให้ท่านสามารถเจาะลึกผลสัมฤทธิ์ของสารสนเทศได้อย่างแม่นยำ รวดเร็ว และคงความน่าเชื่อถือของข้อมูลสูงสุด
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. แผงควบคุมกล่องการ์ดสรุปแต้มสะสมภาพรวมหลัก (Global System Stats Cards) -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white" style="border-left: 5px solid #e5a91e !important;">
                <div class="card-body d-flex justify-content-between align-items-center p-3.5">
                    <div>
                        <span class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">สารสนเทศในระบบ</span>
                        <h3 class="fw-bold mb-0 text-dark" style="font-family: 'Kanit', sans-serif;">
                            {{ number_format($totalContents) }} <span class="text-muted" style="font-size: 0.85rem; font-weight: normal;">รายการ</span>
                        </h3>
                    </div>
                    <div class="bg-warning bg-opacity-10 p-3 rounded-4 text-warning"><i class="bi bi-file-earmark-text-fill fs-3"></i></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white" style="border-left: 5px solid #10b981 !important;">
                <div class="card-body d-flex justify-content-between align-items-center p-3.5">
                    <div>
                        <span class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">ผู้เข้าชมระบบรวมสะสม</span>
                        <h3 class="fw-bold mb-0 text-dark" style="font-family: 'Kanit', sans-serif;">
                            {{ number_format($totalTraffic) }} <span class="text-muted" style="font-size: 0.85rem; font-weight: normal;">ครั้ง</span>
                        </h3>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded-4 text-success"><i class="bi bi-eye-fill fs-3"></i></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white" style="border-left: 5px solid #3b82f6 !important;">
                <div class="card-body d-flex justify-content-between align-items-center p-3.5">
                    <div>
                        <span class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">ดาวน์โหลดเอกสารรวม</span>
                        <h3 class="fw-bold mb-0 text-dark" style="font-family: 'Kanit', sans-serif;">
                            {{ number_format($totalDownloads) }} <span class="text-muted" style="font-size: 0.85rem; font-weight: normal;">ครั้ง</span>
                        </h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded-4 text-primary"><i class="bi bi-file-earmark-arrow-down-fill fs-3"></i></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white" style="border-left: 5px solid #f43f5e !important;">
                <div class="card-body d-flex justify-content-between align-items-center p-3.5">
                    <div>
                        <span class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">ยอดการแชร์ส่งต่อรวม</span>
                        <h3 class="fw-bold mb-0 text-dark" style="font-family: 'Kanit', sans-serif;">
                            {{ number_format($totalShares) }} <span class="text-muted" style="font-size: 0.85rem; font-weight: normal;">แชร์</span>
                        </h3>
                    </div>
                    <div class="bg-danger bg-opacity-10 p-3 rounded-4 text-danger"><i class="bi bi-share-fill fs-3"></i></div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. ส่วนวิเคราะห์กราฟเส้นรายเดือน (Line Chart Section - สรุปยอดเข้าชม ปะทะ ยอดแชร์ ปะทะ ยอดดาวน์โหลด) -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-xl-8">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark" style="font-family: 'Kanit', sans-serif;">
                            <i class="bi bi-graph-up text-warning me-2"></i>เทรนด์สรุปยอดผู้เข้าชม ปะทะ ยอดการแชร์ ปะทะ ยอดดาวน์โหลดรายเดือน
                        </h5>
                        <small class="text-muted">ปีงบประมาณประจำ พ.ศ. {{ $trendYear + 543 }}</small>
                    </div>
                    
                    <form action="{{ url()->current() }}" method="GET" id="trendChartFilterForm" class="d-flex align-items-center gap-2">
                        <input type="hidden" name="perf_year" value="{{ $perfYear }}">
                        <input type="hidden" name="perf_month" value="{{ $perfMonth }}">
                        <label for="trendYearSelect" class="text-nowrap small fw-bold text-secondary font-heading">ระบุปีเทรนด์:</label>
                        <select name="trend_year" id="trendYearSelect" class="form-select form-select-sm border-secondary-subtle rounded-3 fw-bold bg-light" style="width: 120px;" onchange="document.getElementById('trendChartFilterForm').submit();">
                            @foreach($availableYears as $year)
                                <option value="{{ $year }}" {{ $trendYear == $year ? 'selected' : '' }}>พ.ศ. {{ $year + 543 }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <div class="card-body px-4 pb-4">
                    <div style="position: relative; height: 320px; width: 100%;">
                        <canvas id="unifiedLineTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- รายการกล่องประวัติอัปเดตบทความสารสนเทศล่าสุด -->
        <div class="col-12 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark" style="font-family: 'Kanit', sans-serif;">
                        <i class="bi bi-clock-history text-secondary me-2"></i>รายการอัปเดตล่าสุด
                    </h5>
                    <a href="{{ route('admin.contents.index') }}" class="btn btn-xs text-primary btn-light fw-bold px-2.5 py-1 small rounded-3">คลังทั้งหมด</a>
                </div>
                <div class="card-body px-4 pb-4" style="max-height: 330px; overflow-y: auto;">
                    <div class="d-flex flex-column gap-3 mt-2">
                        @forelse($recentArtifacts as $artifact)
                        <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-4 border border-light-subtle">
                            <div class="bg-warning bg-opacity-25 text-warning p-2 rounded-3">
                                <i class="bi bi-file-earmark-text fs-5"></i>
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="fw-bold text-dark text-truncate mb-1" style="font-size: 0.92rem;">
                                    {{ $artifact->title }}
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted d-flex flex-wrap gap-2" style="font-size: 0.78rem;">
                                        <span><i class="bi bi-folder2-open"></i> {{ $artifact->category->name ?? 'หมวดหมู่ทั่วไป' }}</span>
                                        <span><i class="bi bi-calendar3"></i> {{ $artifact->created_at->format('d/m/Y') }}</span>
                                    </small>
                                    <span class="badge {{ $artifact->is_active ? 'bg-success bg-opacity-10 text-success border-success' : 'bg-secondary bg-opacity-10 text-secondary border-secondary' }} border border-opacity-10 px-2 py-1 rounded-pill" style="font-size: 0.68rem;">
                                        <i class="bi {{ $artifact->is_active ? 'bi-check-circle-fill' : 'bi-dash-circle-fill' }} me-1"></i>
                                        {{ $artifact->is_active ? 'เผยแพร่' : 'แบบร่าง' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 mb-2 d-block opacity-40"></i>
                            <small class="fw-bold">ไม่มีรายการความเคลื่อนไหวข้อมูลใหม่</small>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. ส่วนวิเคราะห์กราฟแท่งเปรียบเทียบประสิทธิภาพรายคอนเทนต์ (Top 10 Content Performance) -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 bg-white">
                <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark" style="font-family: 'Kanit', sans-serif;">
                            <i class="bi bi-bar-chart-line-fill text-danger me-2"></i>บทความยอดนิยมสูงสุด 10 อันดับแรก (Top 10 Content Performance)
                        </h5>
                        <small class="text-muted">
                            คัดกรองดัชนีจำแนกชิ้นงานประจำช่วงเวลา: 
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10 fw-bold px-2.5 py-1.5 rounded-3">
                                {{ $perfMonth !== 'all' ? 'เดือน '.$thaiMonths[$perfMonth] : 'ภาพรวมทุกเดือน' }} พ.ศ. {{ $perfYear + 543 }}
                            </span>
                        </small>
                    </div>

                    <form action="{{ url()->current() }}" method="GET" id="performanceChartFilterForm" class="d-flex flex-wrap align-items-center gap-2">
                        <input type="hidden" name="trend_year" value="{{ $trendYear }}">
                        
                        <div class="d-flex align-items-center gap-1.5">
                            <select name="perf_month" id="perfMonthSelect" class="form-select form-select-sm border-secondary-subtle rounded-3 fw-bold bg-light" style="width: 140px;" onchange="document.getElementById('performanceChartFilterForm').submit();">
                                <option value="all" {{ $perfMonth == 'all' ? 'selected' : '' }}>-- ทุกเดือน --</option>
                                @foreach($thaiMonths as $num => $name)
                                    <option value="{{ $num }}" {{ $perfMonth == $num ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="d-flex align-items-center gap-1.5">
                            <select name="perf_year" id="perfYearSelect" class="form-select form-select-sm border-secondary-subtle rounded-3 fw-bold bg-light" style="width: 120px;" onchange="document.getElementById('performanceChartFilterForm').submit();">
                                @foreach($availableYears as $year)
                                    <option value="{{ $year }}" {{ $perfYear == $year ? 'selected' : '' }}>พ.ศ. {{ $year + 543 }}</option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
                <div class="card-body px-4 pb-4">
                    @if(count($chartContentLabels) > 0)
                        <div style="position: relative; height: 360px; width: 100%;">
                            <canvas id="contentGroupedBarChart"></canvas>
                        </div>
                    @else
                        <div class="text-center py-5 my-4 text-muted border border-dashed rounded-4 bg-light">
                            <i class="bi bi-bar-chart-steps fs-1 text-secondary opacity-40 d-block mb-2"></i>
                            <h6 class="fw-bold text-dark mb-1">ไม่พบข้อมูลดัชนีชี้วัดประสิทธิภาพในมิติเวลานี้</h6>
                            <p class="small text-muted mb-0">เนื่องจากไม่มีการจัดสร้างเนื้อหาขึ้นในระยะเวลาที่คัดกรองระบบ</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('admin_scripts')
<script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        
        // 📈 [Chart 1] ระบบพล็อตโครงสร้างกราฟเส้นสรุปยอดเทรนด์รวมรายเดือน (ยอดเข้าชม vs ยอดแชร์ vs ยอดดาวน์โหลด)
        const lineCtx = document.getElementById('unifiedLineTrendChart');
        if (lineCtx) {
            new Chart(lineCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'],
                    datasets: [
                        {
                            label: 'ยอดการเข้าชมระบบ (ครั้ง)',
                            data: {!! json_encode($monthlyViews) !!},
                            borderColor: '#e5a91e',
                            backgroundColor: 'rgba(229, 169, 30, 0.10)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#fff',
                            pointBorderColor: '#e5a91e',
                            pointRadius: 4
                        },
                        {
                            label: 'ยอดการกดแชร์ส่งต่อ (ครั้ง)',
                            data: {!! json_encode($monthlyShares) !!},
                            borderColor: '#f43f5e',
                            backgroundColor: 'rgba(244, 63, 94, 0.04)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#fff',
                            pointBorderColor: '#f43f5e',
                            pointRadius: 4
                        },
                        {
                            label: 'ยอดการดาวน์โหลดเอกสาร (ครั้ง)',
                            data: {!! json_encode($monthlyDownloads) !!},
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.06)',
                            borderWidth: 2.5,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#fff',
                            pointBorderColor: '#3b82f6',
                            pointRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top', labels: { font: { family: "'Sarabun', sans-serif", weight: 'bold' }, usePointStyle: true } }
                    },
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { font: { family: "'Sarabun', sans-serif" } } },
                        x: { grid: { display: false }, ticks: { font: { family: "'Sarabun', sans-serif" } } }
                    },
                    interaction: { intersect: false, mode: 'index' }
                }
            });
        }

        // 📊 [Chart 2] ระบบพล็อตโครงสร้างกราฟแท่งเปรียบเทียบชิ้นงานจริง Top 10 (พร้อมยอดดาวน์โหลด)
        const barCtx = document.getElementById('contentGroupedBarChart');
        if (barCtx) {
            new Chart(barCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode($chartContentLabels) !!},
                    datasets: [
                        {
                            label: 'ยอดวิวกิจกรรม/บทความ',
                            data: {!! json_encode($chartContentViews) !!},
                            backgroundColor: '#e5a91e',
                            borderRadius: 6,
                            barPercentage: 0.6,
                            categoryPercentage: 0.8
                        },
                        {
                            label: 'ยอดดาวน์โหลดเอกสาร',
                            data: {!! json_encode($chartContentDownloads) !!},
                            backgroundColor: '#3b82f6',
                            borderRadius: 6,
                            barPercentage: 0.6,
                            categoryPercentage: 0.8
                        },
                        {
                            label: 'ยอดการแชร์โซเชียล',
                            data: {!! json_encode($chartContentShares) !!},
                            backgroundColor: '#f43f5e',
                            borderRadius: 6,
                            barPercentage: 0.6,
                            categoryPercentage: 0.8
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    plugins: {
                        legend: { 
                            position: 'top', 
                            labels: { font: { family: "'Sarabun', sans-serif", weight: 'bold' }, usePointStyle: true } 
                        },
                        tooltip: {
                            enabled: true,
                            backgroundColor: 'rgba(30, 41, 59, 0.95)',
                            titleFont: { family: "'Kanit', sans-serif", size: 14, weight: 'bold' },
                            bodyFont: { family: "'Sarabun', sans-serif", size: 13 },
                            padding: 12,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += new Intl.NumberFormat().format(context.parsed.y) + ' ครั้ง';
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { font: { family: "'Sarabun', sans-serif" } } },
                        x: { grid: { display: false }, ticks: { font: { family: "'Sarabun', sans-serif", size: 11 }, maxRotation: 20, minRotation: 0 } }
                    }
                }
            });
        }

    });
</script>
@endpush