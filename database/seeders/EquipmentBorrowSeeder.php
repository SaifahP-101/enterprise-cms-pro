<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EquipmentBorrow;
use Carbon\Carbon;
use Faker\Factory as Faker;

class EquipmentBorrowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create('th_TH');
        
        $statuses = [
            EquipmentBorrow::STATUS_STUDENT, 
            EquipmentBorrow::STATUS_STAFF, 
            EquipmentBorrow::STATUS_EXTERNAL
        ];
        
        $faculties = ['คณะมนุษยศาสตร์ฯ', 'คณะครุศาสตร์', 'คณะวิทยาศาสตร์ฯ', 'คณะเทคโนโลยีสารสนเทศ', 'สำนักงานอธิการบดี'];
        $equipments = ['กล้อง DSLR', 'ชุดไมโครโฟนไร้สาย', 'ไฟ Spotlight', 'ชุดไทยประยุกต์', 'เครื่องขยายเสียงแบบพกพา', 'ฉากกั้นเวที'];

        // สร้างข้อมูลสุ่ม 100 รายการ ย้อนหลัง 3 เดือน
        for ($i = 0; $i < 100; $i++) {
            $borrowDate = Carbon::now()->subDays(rand(1, 90));
            $returnDate = (clone $borrowDate)->addDays(rand(1, 7));
            
            EquipmentBorrow::create([
                'borrower_name' => $faker->name,
                'borrower_status' => $faker->randomElement($statuses),
                'faculty_department' => $faker->randomElement($faculties),
                'phone_number' => $faker->numerify('08########'),
                'equipment_name' => $faker->randomElement($equipments),
                'quantity' => rand(1, 5),
                'borrow_date' => $borrowDate,
                'expected_return_date' => $returnDate,
                'purpose' => 'ใช้สำหรับงานกิจกรรมนักศึกษา',
                'created_at' => $borrowDate, // ให้ created_at ตรงกับ borrow_date เพื่อความสมจริงใน Log
                'updated_at' => $borrowDate,
            ]);
        }
    }
}