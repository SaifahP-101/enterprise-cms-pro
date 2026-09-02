<table>
    <thead>
        <tr>
            <th colspan="3" style="font-weight: bold; font-size: 14px; text-align: center;">
                รายงานสถิติการยืมอุปกรณ์และครุภัณฑ์
            </th>
        </tr>
        <tr>
            <th colspan="3" style="text-align: center;">
                ตั้งแต่วันที่ {{ $startDate }} ถึง {{ $endDate }}
            </th>
        </tr>
        <tr>
            <th>ข้อมูลสรุป</th>
            <th>จำนวน</th>
            <th>หน่วย</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>จำนวนครั้งการยืมรวม</td>
            <td>{{ $stats['total_borrows'] }}</td>
            <td>ครั้ง</td>
        </tr>
        <tr>
            <td>จำนวนอุปกรณ์ที่ถูกยืมรวม</td>
            <td>{{ $stats['total_quantity'] }}</td>
            <td>ชิ้น</td>
        </tr>
        
        <tr><td colspan="3"></td></tr>
        <tr>
            <td colspan="3" style="font-weight: bold;">สถิติแยกตามประเภทผู้ใช้</td>
        </tr>
        @foreach($stats['user_types'] as $type)
        <tr>
            <td>{{ $type->borrower_status }}</td>
            <td>{{ $type->total }}</td>
            <td>ครั้ง</td>
        </tr>
        @endforeach

        <tr><td colspan="3"></td></tr>
        <tr>
            <td colspan="3" style="font-weight: bold;">สถิติแยกตามคณะ/หน่วยงาน</td>
        </tr>
        @foreach($stats['faculties'] as $faculty)
        <tr>
            <td>{{ $faculty->faculty_department }}</td>
            <td>{{ $faculty->total }}</td>
            <td>ครั้ง</td>
        </tr>
        @endforeach

        <tr><td colspan="3"></td></tr>
        <tr>
            <td colspan="3" style="font-weight: bold;">รายการอุปกรณ์ที่ถูกยืม</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">ชื่ออุปกรณ์</td>
            <td style="font-weight: bold;">จำนวนครั้งที่ยืม</td>
            <td style="font-weight: bold;">จำนวนชิ้นรวม</td>
        </tr>
        @foreach($stats['equipments'] as $eq)
        <tr>
            <td>{{ $eq->equipment_name }}</td>
            <td>{{ $eq->total_times }}</td>
            <td>{{ $eq->total_qty }}</td>
        </tr>
        @endforeach
    </tbody>
</table>