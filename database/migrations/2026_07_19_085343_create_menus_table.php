<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment('ชื่อเมนูแสดงผล เช่น หน้าแรก, เกี่ยวกับองค์กร');
            $table->string('url')->nullable()->comment('ลิงก์ปลายทาง หรือ Route Path');
            
            /**
             * 🔗 Self-Referencing Foreign Key:
             * ผูกไอดีเชื่อมโยงกลับหาตารางตัวเองตัวมันเองเพื่อทำชั้นเมนูแตกกิ่ง Parent-Child
             * หากเมนูหลัก (Parent) ถูกลบ เมนูย่อย (Children) จะถูกกวาดล้างออกออโต้ด้วยกลไก Cascade
             */
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->foreign('parent_id')
                  ->references('id')
                  ->on('menus')
                  ->onDelete('cascade');

            $table->integer('sort_order')->default(0)->comment('ลำดับจัดเรียงซ้าย-ขวา หรือบน-ลงล่าง');
            $table->boolean('is_active')->default(true)->comment('สถานะเปิด/ปิดซ่อนเมนู');
            $table->timestamps();

            // ⚡ Composite Indexing สำหรับเร่งสปีดการคัดกรองเมนูเปิดใช้งานหลัก
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
        Schema::dropIfExists('menus');
    }
}