<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * หยอดข้อมูลหมวดหมู่หลัก 14 กลุ่ม พร้อมข้อกำหนดขนาดและรูปแบบรูปภาพ
     */
    public function run()
    {
        // 1. เคลียร์ข้อมูลเก่าและปิดการเช็ค Foreign Key ชั่วคราวเพื่อป้องกัน Error ตอน Delete
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Category::query()->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. กำหนดชุดข้อมูลหมวดหมู่หลัก 14 รายการพร้อม Image Specifications
        $categories = [
            [
                'name'            => 'ข่าวประชาสัมพันธ์',
                'slug'            => 'news-and-announcements',
                'image_size'      => '2MB',
                'image_dimension' => '1200 x 630 Pixels',
                'image_type'      => '.JPG .png',
                'sort_order'      => 1,
                'is_active'       => true,
            ],
            [
                'name'            => 'โครงการ/กิจกรรม',
                'slug'            => 'projects-and-activities',
                'image_size'      => '2MB',
                'image_dimension' => '1200 x 630 Pixels',
                'image_type'      => '.JPG .png',
                'sort_order'      => 2,
                'is_active'       => true,
            ],
            [
                'name'            => 'กิจกรรมหน่วยอนุรักษ์ฯ',
                'slug'            => 'conservation-unit-activities',
                'image_size'      => '2MB',
                'image_dimension' => '1200 x 630 Pixels',
                'image_type'      => '.JPG .png',
                'sort_order'      => 3,
                'is_active'       => true,
            ],
            [
                'name'            => 'งานวิจัยและบทความ',
                'slug'            => 'research-and-articles',
                'image_size'      => '2MB',
                'image_dimension' => '800 x 1200 Pixels',
                'image_type'      => '.JPG .png',
                'sort_order'      => 4,
                'is_active'       => true,
            ],
            [
                'name'            => 'หนังสือและวารสารสำนักฯ',
                'slug'            => 'books-and-journals',
                'image_size'      => '2MB',
                'image_dimension' => '800 x 1200 Pixels',
                'image_type'      => '.JPG .png',
                'sort_order'      => 5,
                'is_active'       => true,
            ],
            [
                'name'            => 'แหล่งเรียนรู้ 3 บุรี',
                'slug'            => 'three-buri-learning-resources',
                'image_size'      => '2MB',
                'image_dimension' => '1200 x 630 Pixels',
                'image_type'      => '.JPG .png',
                'sort_order'      => 6,
                'is_active'       => true,
            ],
            [
                'name'            => 'จดหมายข่าว',
                'slug'            => 'newsletters',
                'image_size'      => '2MB',
                'image_dimension' => '800 x 1200 Pixels',
                'image_type'      => '.JPG .png',
                'sort_order'      => 7,
                'is_active'       => true,
            ],
            [
                'name'            => 'งานประกันคุณภาพ',
                'slug'            => 'quality-assurance-works',
                'image_size'      => '2MB',
                'image_dimension' => '1200 x 630 Pixels',
                'image_type'      => '.JPG .png',
                'sort_order'      => 8,
                'is_active'       => true,
            ],
            [
                'name'            => 'เครือข่ายความร่วมมือ',
                'slug'            => 'collaboration-networks',
                'image_size'      => '2MB',
                'image_dimension' => '1200 x 630 Pixels',
                'image_type'      => '.JPG .png',
                'sort_order'      => 9,
                'is_active'       => true,
            ],
            [
                'name'            => 'ประกันคุณภาพการศึกษา',
                'slug'            => 'educational-quality-assurance',
                'image_size'      => '2MB',
                'image_dimension' => '800 x 1200 Pixels',
                'image_type'      => '.JPG .png',
                'sort_order'      => 10,
                'is_active'       => true,
            ],
            [
                'name'            => 'พระนารายณ์ศึกษา',
                'slug'            => 'phra-narai-studies',
                'image_size'      => '2MB',
                'image_dimension' => '800 x 1200 Pixels',
                'image_type'      => '.JPG .png',
                'sort_order'      => 11,
                'is_active'       => true,
            ],
            [
                'name'            => 'หอวัฒนธรรม',
                'slug'            => 'cultural-hall',
                'image_size'      => '2MB',
                'image_dimension' => '1200 x 630 Pixels',
                'image_type'      => '.JPG .png',
                'sort_order'      => 12,
                'is_active'       => true,
            ],
            [
                'name'            => 'ข้อมูลสาธารณะ',
                'slug'            => 'public-information',
                'image_size'      => '2MB',
                'image_dimension' => '1200 x 630 Pixels',
                'image_type'      => '.JPG .png',
                'sort_order'      => 13,
                'is_active'       => true,
            ],
            [
                'name'            => 'จดหมายที่เกียวข้อง',
                'slug'            => 'related-letters',
                'image_size'      => '2MB',
                'image_dimension' => '1200 x 630 Pixels',
                'image_type'      => '.JPG .png',
                'sort_order'      => 14,
                'is_active'       => true,
            ],
        ];

        // 3. บันทึกข้อมูลลงฐานข้อมูล
        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}