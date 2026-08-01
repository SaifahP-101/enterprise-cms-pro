<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use App\Models\Menu;
use Illuminate\Support\Facades\Cache;

class MenuComposer
{
    /**
     * Bind ข้อมูลโครงสร้างเมนูลงสู่หน้าต่าง View ที่กำหนด
     *
     * @param  \Illuminate\View\View  $view
     * @return void
     */
    public function compose(View $view)
    {
        /**
         * ⚡ ENTERPRISE CACHE TUNING:
         * ทำการกักเก็บโครงสร้างผังเมนูแตกกิ่งไว้ในหน่วยความจำแคชระบบไร้กำหนดเวลา 
         * เพื่อบล็อกการวิ่งไปยิงคำสั่ง SELECT ตารางเมนูใน MySQL Container ทุกๆ ครั้งที่มีคนเปิดหน้าเว็บ
         * ตัวแคชนี้จะถูกสลักทำลายทิ้งออโต้ทันทีเมื่อแอดมินกด อัปเดต/ลบ เมนูที่หลังบ้าน (ด้วยลอจิก Observer ในโมเดล Menu)
         */
        $navigationTree = Cache::rememberForever('frontend_navigation_tree', function () {
            return Menu::with(['children' => function ($query) {
                            $query->where('is_active', true)->orderBy('sort_order', 'asc');
                        }])
                        ->whereNull('parent_id')
                        ->where('is_active', true)
                        ->orderBy('sort_order', 'asc')
                        ->get();
        });

        // ฉีดพ่นตัวแปร $navigationTree พร้อมใช้งานในระดับ Template
        $view->with('navigationTree', $navigationTree);
    }
}