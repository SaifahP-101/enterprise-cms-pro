<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContentGalleriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('content_galleries', function (Blueprint $table) {
            $table->id();
            
            /**
             * 🔗 Master-Detail Constraint:
             * ผูก Foreign Key ตรงไปยังตารางแม่ 'contents'
             * เมื่อบทความหลักถูกลบถาวร (Force Delete) อัลบั้มรูปภาพย่อยชุดนี้จะโดนกวาดล้างออกจาก DB อัตโนมัติ
             */
            $table->foreignId('content_id')
                  ->constrained('contents')
                  ->onDelete('cascade');

            $table->string('file_path'); // พาธสถิตสําหรับเก็บไฟล์รูปภาพกิจกรรมใน Local Storage
            $table->integer('sort_order')->default(0); // ลำดับจัดเรียงภาพถ่ายในการแสดงผลหน้าสไลด์โชว์
            $table->timestamps();

            /**
             * ⚡ Database Indexing Optimization:
             * ทำดัชนีคีย์สืบค้นบนฟิลด์ content_id เพื่อเร่งสปีดคิวรีในการดึงภาพอัลบั้มกิจกรรม
             * ช่วยให้ MySQL คอนเทนเนอร์ทำงานได้เร็วขึ้นเมื่อสารสนเทศขยายตัวระดับแสนเรคคอร์ด
             */
            $table->index('content_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('content_galleries');
    }
}