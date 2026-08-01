<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 🔒 1. ปิดการตรวจสอบ Foreign Key ชั่วคราวเพื่อทำความสะอาดตารางเดิม
        Schema::disableForeignKeyConstraints();
        Menu::truncate();
        Schema::enableForeignKeyConstraints();

        // 🧹 2. เคลียร์ Cache เมนูหน้าบ้าน
        Cache::forget('frontend_menus_cache');

        // 🗂️ 3. โครงสร้างเมนูสารสนเทศองค์กรตามข้อกำหนด
        $menuTree = [
            [
                'title' => 'หน้าแรก',
                'url' => '/',
                'children' => []
            ],
            [
                'title' => 'แนะนำ',
                'url' => '#',
                'children' => [
                    ['title' => 'ประวัติความเป็นมา', 'url' => '/page/history'],
                    ['title' => 'ปรัชญา วิสัยทัศน์ พันธกิจ', 'url' => '/page/vision-mission'],
                    ['title' => 'ตราสัญลักษณ์', 'url' => '/page/logo'],
                    ['title' => 'นโยบายและแผนยุทธศาสตร์', 'url' => '/page/strategic-plan'],
                    ['title' => 'เครือข่ายความร่วมมือ', 'url' => '/category/collaboration-networks'],
                    ['title' => 'ประกันคุณภาพการศึกษา/รายงานประจำปี', 'url' => '/category/educational-quality-assurance'],
                ]
            ],
            [
                'title' => 'การบริหาร',
                'url' => '#',
                'children' => [
                    ['title' => 'โครงสร้างองค์กร', 'url' => '/page/organization-chart'],
                    ['title' => 'ทำเนียบผู้บริหาร', 'url' => '/page/executives'],
                    ['title' => 'คณะกรรมการประจำสำนัก', 'url' => '/page/board-members'],
                    ['title' => 'บุคลากร', 'url' => '/page/staff'],
                ]
            ],
            [
                'title' => 'ศูนย์ข้อมูลศิลปวัฒนธรรม',
                'url' => '#',
                'children' => [
                    ['title' => 'หนังสือและวารสารสำนักฯ', 'url' => '/category/books-and-journals'],
                    ['title' => 'งานวิจัยและบทความ', 'url' => '/category/research-and-articles'],
                    ['title' => 'แหล่งเรียนรู้', 'url' => '/category/three-buri-learning-resources'],
                    ['title' => 'โครงการ/กิจกรรม', 'url' => '/category/projects-and-activities'],
                    ['title' => 'พระนารายณ์ศึกษา', 'url' => '/category/phra-narai-studies'],
                    ['title' => 'หอวัฒนธรรม', 'url' => '/category/cultural-hall'],
                ]
            ],
            [
                'title' => 'หน่วยอนุรักษ์ฯ',
                'url' => '#',
                'children' => [
                    ['title' => 'ความเป็นมา', 'url' => '/page/conservation-history'],
                    ['title' => 'บทบาทหน้าที่', 'url' => '/page/conservation-roles'],
                    ['title' => 'คณะกรรมการ', 'url' => '/page/conservation-board'],
                    ['title' => 'กิจกรรมหน่วยอนุรักษ์ฯ', 'url' => '/category/conservation-unit-activities'],
                    ['title' => 'ฐานข้อมูลหน่วยอนุรักษ์ฯ', 'url' => '/page/conservation-database'],
                ]
            ],
            [
                'title' => 'จดหมายข่าว',
                'url' => '/category/newsletters',
                'children' => []
            ],
            [
                'title' => 'ประชาสัมพันธ์',
                'url' => '/category/news-and-announcements',
                'children' => []
            ],
            [
                'title' => 'แผนงาน',
                'url' => '/page/operational-plan',
                'children' => []
            ],
            [
                'title' => 'การจัดการความรู้',
                'url' => '/page/knowledge-management',
                'children' => []
            ],
            [
                'title' => 'ประกันคุณภาพ',
                'url' => '/category/quality-assurance-works',
                'children' => []
            ],
            [
                'title' => 'ติดต่อเรา',
                'url' => '/contact',
                'children' => []
            ],
        ];

        // 🔄 4. บันทึกข้อมูลลงตาราง menus พร้อมสร้าง Parent-Child Relationship
        $mainSortOrder = 1;

        foreach ($menuTree as $mainItem) {
            // สร้างเมนูหลัก (Root Level)
            $parentMenu = Menu::create([
                'title'      => $mainItem['title'],
                'url'        => $mainItem['url'],
                'parent_id'  => null,
                'sort_order' => $mainSortOrder++,
                'is_active'  => true,
            ]);

            // หากมีเมนูย่อย (Sub-menus) ให้สร้างและผูก parent_id กลับไปหาเมนูหลัก
            if (!empty($mainItem['children'])) {
                $subSortOrder = 1;
                foreach ($mainItem['children'] as $childItem) {
                    Menu::create([
                        'title'      => $childItem['title'],
                        'url'        => $childItem['url'],
                        'parent_id'  => $parentMenu->id,
                        'sort_order' => $subSortOrder++,
                        'is_active'  => true,
                    ]);
                }
            }
        }
    }
}