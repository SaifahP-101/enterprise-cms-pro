<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSlideshowsTable extends Migration
{
    public function up()
    {
        Schema::create('slideshows', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable()->comment('ข้อความอธิบายแบนเนอร์ (สำหรับ alt tag SEO)');
            $table->string('image_path')->comment('พาร์ทจัดเก็บรูปภาพแบนเนอร์ในระบบ');
            $table->string('link_url')->nullable()->comment('ลิงก์ปลายทางเมื่อคลิกรูปภาพ');
            $table->integer('sort_order')->default(0)->comment('ลำดับการแสดงผล');
            $table->boolean('is_active')->default(true)->comment('สถานะเปิด/ปิดสไลด์');
            $table->timestamps();

            // ⚡ Performance Setup: ดัชนีผสมเร่งสปีดคิวรีหน้าแรก (หาเฉพาะที่ Active และเรียงตามลำดับ)
            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('slideshows');
    }
}