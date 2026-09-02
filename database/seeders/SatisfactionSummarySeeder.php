<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SatisfactionSummary;

class SatisfactionSummarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // เคลียร์ข้อมูลเก่าก่อน (ป้องกันข้อมูลซ้ำหากรันคำสั่ง db:seed ซ้ำ)
        SatisfactionSummary::truncate();

        $summaries = [
            [
                'period'             => 'ประจำปีงบประมาณ 2565',
                'overall_rating'     => 4.35,
                'total_respondents'  => 850,
                'dimension_service'  => 85,
                'dimension_staff'    => 88,
                'dimension_facility' => 80,
                'is_published'       => false, // เก็บเป็นข้อมูลอดีต
                'created_at'         => now()->subYears(2),
                'updated_at'         => now()->subYears(2),
            ],
            [
                'period'             => 'ประจำปีงบประมาณ 2566',
                'overall_rating'     => 4.62,
                'total_respondents'  => 1120,
                'dimension_service'  => 92,
                'dimension_staff'    => 95,
                'dimension_facility' => 90,
                'is_published'       => false, // เก็บเป็นข้อมูลอดีต
                'created_at'         => now()->subYear(),
                'updated_at'         => now()->subYear(),
            ],
            [
                'period'             => 'ครึ่งปีแรก ประจำปีงบประมาณ 2567',
                'overall_rating'     => 4.85,
                'total_respondents'  => 1250,
                'dimension_service'  => 96,
                'dimension_staff'    => 98,
                'dimension_facility' => 94,
                'is_published'       => true, // เปิดแสดงผลหน้าเว็บไซต์ (ตัวล่าสุด)
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
        ];

        // วนลูปบันทึกข้อมูลเข้า Database (Event ของ Model จะทำการ Clear Cache อัตโนมัติด้วย)
        foreach ($summaries as $summary) {
            SatisfactionSummary::create($summary);
        }

        $this->command->info('✅ สร้างข้อมูลจำลอง (Seed Data) สำหรับ Satisfaction Summary สำเร็จแล้ว!');
    }
}