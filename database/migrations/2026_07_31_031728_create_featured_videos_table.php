<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeaturedVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('featured_videos', function (Blueprint $table) {
            $table->id();
            $table->string('title');                          // หัวข้อวิดีโอแนะนำ
            $table->text('description')->nullable();          // คำอธิบาย/รายละเอียดสังเขป
            $table->string('youtube_url');                   // ลิงก์ YouTube ต้นทาง
            $table->string('youtube_id', 50)->nullable();    // ID รหัสวิดีโอ YouTube (แกะให้อัตโนมัติ)
            $table->string('custom_thumbnail')->nullable();  // ภาพปกกำหนดเอง (ถ้ามี)
            $table->integer('sort_order')->default(0)->index(); // ลำดับการแสดงผล
            $table->boolean('is_active')->default(true)->index(); // สถานะเปิด/ปิดการแสดงผล
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('featured_videos');
    }
}