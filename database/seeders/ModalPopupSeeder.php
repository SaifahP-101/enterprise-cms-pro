<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ModalPopup;
use Carbon\Carbon;

class ModalPopupSeeder extends Seeder
{
    public function run()
    {
        ModalPopup::query()->delete();

        // สร้างป๊อปอัปแจ้งเตือนที่เปิดใช้งานอยู่
        ModalPopup::create([
            'title'      => 'ประกาศรับสมัครนักวิชาการวัฒนธรรม ประจำปี 2569',
            'image_path' => 'placeholders/popup_announce.jpg',
            'link_url'   => 'https://example.com/register',
            'is_active'  => true,
            'start_date' => Carbon::now()->subDays(1),
            'end_date'   => Carbon::now()->addDays(15), // สิ้นสุดในอีก 15 วันข้างหน้า
        ]);
    }
}