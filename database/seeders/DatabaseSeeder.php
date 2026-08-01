<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // 🛡️ ปลดล็อก Foreign Key ชั่วคราวเพื่อให้สามารถ Truncate หรือสาดข้อมูลทับได้โดยไม่ติด Error
        Schema::disableForeignKeyConstraints();

        $this->call([ 
            UserSeeder::class,
            MenuSeeder::class,
            CategorySeeder::class,
            TagSeeder::class,
            ContentSeeder::class,
            SlideshowSeeder::class,
            ModalPopupSeeder::class,
        ]);

        // 🔒 ล็อก Foreign Key กลับสู่สภาพความปลอดภัยเดิม
        Schema::enableForeignKeyConstraints();
    }
}