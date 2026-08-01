<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            
            /**
             * ⚡ SEO Optimization: 
             * ทำ Unique Index สำหรับฟิลด์ Slug เพื่อรองรับ URL string 
             * ช่วยป้องกันชื่อ URL ซ้ำซ้อนและเร่งความเร็วในการค้นหาหน้าเว็บแบบเจาะจง
             */
            $table->string('slug')->unique(); 
            
            $table->integer('sort_order')->default(0); // ลำดับการจัดเรียงเมนูหรือหมวดหมู่จากหลังบ้าน
            $table->boolean('is_active')->default(true); // สวิตช์เปิด-ปิดการใช้งานหมวดหมู่
            $table->timestamps(); // เจนเนอเรตฟิลด์ created_at และ updated_at

            /**
             * 📊 Performance Indexing:
             * ทำ Index แบบคู่ (Composite Index) บนสถานะการใช้งานลำดับการเรียง 
             * เพื่อให้หน้าบ้านคิวรีดึงเมนูหมวดหมู่ไปโชว์ได้เร็วที่สุดโดยไม่ต้อง Scan ทั้งตาราง
             */
            $table->index(['is_active', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('categories');
    }
}