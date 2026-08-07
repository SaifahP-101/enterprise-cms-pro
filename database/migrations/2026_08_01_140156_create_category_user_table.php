<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateCategoryUserTable extends Migration
{
    public function up()
    {
        // 1. สร้างตาราง Pivot category_user
        if (!Schema::hasTable('category_user')) {
            Schema::create('category_user', function (Blueprint $table) {
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
                $table->primary(['user_id', 'category_id']);
            });
        }

        // 2. เพิ่ม Permission สำคัญสำหรับเข้าถึงได้ทุกหมวดหมู่ (ถ้ายังไม่มี)
        if (Schema::hasTable('permissions')) {
            DB::table('permissions')->updateOrInsert(
                ['slug' => 'manage_all_categories'],
                [
                    'name'        => 'จัดการบทความได้ทุกหมวดหมู่ (Unrestricted)',
                    'module'      => 'contents',
                    'description' => 'สามารถสร้าง แก้ไข และลบบทความได้ทุกหมวดหมู่โดยไม่มีข้อจำกัด',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]
            );
        }
    }

    public function down()
    {
        Schema::dropIfExists('category_user');
    }
}