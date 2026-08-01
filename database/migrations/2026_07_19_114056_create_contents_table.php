<?php
// database/migrations/2026_07_19_114056_create_contents_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            
            // 🔗 ความสัมพันธ์หลัก Many-to-One ไปยังตารางหมวดหมู่
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            
            $table->string('title');
            $table->string('slug')->unique(); // ทำ Unique Index รองรับโครงสร้าง URL ภาษาไทยสำหรับคะแนน SEO
            $table->string('type');           // ข้อจำกัด PHP 8.0: ใช้ String คุมค่าแทน Enum ('NEWS', 'ACTIVITY', 'ANNOUNCEMENT')
            $table->longText('body');         // รองรับการป้อนข้อมูลประณีตจาก CKEditor 5
            
            // 🖼️ มีเดียและไฟล์แนบคุ้มครองความปลอดภัยขั้นสูง
            $table->string('cover_image')->nullable();
            $table->string('secure_pdf_path')->nullable(); // ย้ายไปสถิตอยู่พาร์ทปิดชั้นใน ไม่ใช้ ->after() แล้ว
            $table->string('youtube_url')->nullable();      // รองรับลิงก์สำหรับถอดรหัสเรนเดอร์ ID หน้าเว็บ
            
            // ⚡ Smart Metrics & SEO Optimization Fields
            $table->unsignedBigInteger('view_count')->default(0); // รองรับ Anti-F5 Smart View Counter
            $table->string('meta_title')->nullable();             // คุมหัวข้อเจาะจงบน Google Search
            $table->text('meta_description')->nullable();         // คำโปรยสำหรับดึงดูดทราฟฟิกเมื่อแชร์ลง LINE/Facebook

            $table->unsignedBigInteger('user_id')->nullable()->comment('ไอดีผู้บันทึกข้อมูล');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            
            $table->boolean('is_active')->default(true);
            $table->timestamp('published_at')->nullable(); // ระบบตั้งเวลาเผยแพร่ข่าวสารล่วงหน้า
            $table->timestamps();
            
            // 🛡️ เพิ่มฟิลด์ 'deleted_at' สำหรับกลไกถังขยะกู้คืนข้อมูลหลังบ้าน
            $table->softDeletes(); 

            /**
             * ⚡ Ultimate Composite Indexing:
             * ทำดัชนีกลุ่มบนเงื่อนไขคิวรีหน้าร้าน เพื่อเร่งความเร็วในการดึงข้อมูลหน้าแรก 
             * ช่วยรีดความเร็วในระดับมิลลิวินาทีแม้ฐานข้อมูลองค์กรจะมีบทความขยายตัวหลักแสนเรคคอร์ด
             */
            $table->index(['type', 'is_active', 'published_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contents');
    }
}