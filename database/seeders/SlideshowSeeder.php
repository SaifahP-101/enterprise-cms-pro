<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Slideshow;

class SlideshowSeeder extends Seeder
{
    public function run()
    {
        Slideshow::query()->delete();

        Slideshow::create([
            'title'      => 'ยินดีต้อนรับสู่สำนักศิลปะและวัฒนธรรม มรภ.เทพสตรี',
            'image_path' => 'placeholders/slide1.jpg', // จะยังไม่โชว์รูปจริงจนกว่าจะอัปโหลดทับผ่านหลังบ้าน
            'link_url'   => 'https://www.tru.ac.th',
            'sort_order' => 1,
            'is_active'  => true,
        ]);

        Slideshow::create([
            'title'      => 'โครงการฐานข้อมูลภูมิปัญญาท้องถิ่นเมืองลพบุรี',
            'image_path' => 'placeholders/slide2.jpg',
            'link_url'   => null,
            'sort_order' => 2,
            'is_active'  => true,
        ]);
    }
}