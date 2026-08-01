<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AccessControlController extends Controller
{
    /**
     * หน้าจอแดชบอร์ดแสดงผลประวัติระบบหลัก
     */
    public function index()
    {
        return view('admin.access_control.index');
    }

    /**
     * ⚡ HIGH-PERFORMANCE SERVER-SIDE DATATABLES GATEWAY
     * ออกแบบลอจิกคิวรีสดคำนวณแบ่งหน้าผ่าน MySQL Engine แทนการโหลดโมเดลทั้งหมดขึ้น RAM
     */
    public function getLogsData(Request $request): JsonResponse
    {
        // 1. นำเข้าพารามิเตอร์สั่งการหลักจาก JavaScript หน้าบ้าน
        $columns = ['id', 'created_at', 'user_id', 'action', 'auditable_type', 'ip_address'];
        
        $limit = $request->input('length', 10);
        $start = $request->input('start', 0);
        $search = $request->input('search.value');
        
        $orderColumnIndex = $request->input('order.0.column', 1);
        $orderDir = $request->input('order.0.dir', 'desc');
        $orderColumn = $columns[$orderColumnIndex] ?? 'created_at';

        // 2. ตั้งต้นคิวรีพื้นฐาน พ่วงระบบ Eager Loading ผู้ใช้งาน ดักจับปัญหา N+1
        $query = AuditLog::with('user');

        // คำนวณหาจำนวนรวมเรคคอร์ดทั้งหมดก่อนทำการกรองคำค้น
        $totalData = $query->count();

        // 3. ระบบ Dynamic Search: หากผู้ใช้พิมพ์คำค้นหา ให้ทำการกรองชุดข้อมูลทันที
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('action', 'LIKE', "%{$search}%")
                  ->orWhere('auditable_type', 'LIKE', "%{$search}%")
                  ->orWhere('ip_address', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        $totalFiltered = $query->count();

        // 4. สั่งการประมวลผลจัดแบ่งหน้า (Pagination) และจัดเรียงข้อมูลระดับเซิร์ฟเวอร์ฐานข้อมูล
        $logs = $query->offset($start)
                      ->limit($limit)
                      ->orderBy($orderColumn, $orderDir)
                      ->get();

        // 5. ประกอบรูปชุดข้อมูล Mapping Array ป้อนกลับหน้าร้านให้ตรงตามข้อกำหนดสากลของ DataTables Plugin
        $data = [];
        foreach ($logs as $log) {
            $data[] = [
                'id'             => $log->id,
                'created_at'     => $log->created_at->format('d/m/Y H:i:s'),
                'user'           => $log->user->name,
                'action'         => $log->action,
                // แปลงชื่อคลาสยาวเหยียดให้สั้นกระชับอ่านง่ายสบายตาแอดมิน
                'auditable_type' => str_replace('App\\Models\\', '', $log->auditable_type) . ' (ID: ' . $log->auditable_id . ')',
                'ip_address'     => $log->ip_address,
                'details'        => '<button class="btn btn-xs btn-outline-dark view-log-payload py-0.5 px-2 font-weight-bold" style="font-size:0.75rem;" data-old="'.max_encode($log->old_values).'" data-new="'.max_encode($log->new_values).'">🔍 ดู Payload</button>'
            ];
        }

        return response()->json([
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        ]);
    }
}

/**
 * ฟังก์ชันช่วยเข้ารหัสแปลง Array ข้อมูลความปลอดภัยให้หลบหลีกการตัดแท็กของเบราว์เซอร์
 */
function max_encode($value) {
    if (empty($value)) return 'ไม่มีการเปลี่ยนแปลง';
    return htmlspecialchars(json_encode($value, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
}