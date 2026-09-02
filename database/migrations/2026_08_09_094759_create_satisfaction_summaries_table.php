<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSatisfactionSummariesTable extends Migration
{
    public function up()
    {
        Schema::create('satisfaction_summaries', function (Blueprint $table) {
            $table->id();
            $table->string('period', 150); // เช่น "ประจำปีงบประมาณ 2567"
            $table->decimal('overall_rating', 3, 2); // คะแนนเฉลี่ยรวม (เช่น 4.85) เก็บได้สูงสุด 9.99
            $table->unsignedInteger('total_respondents')->default(0); // จำนวนผู้ตอบแบบสอบถามทั้งหมด
            
            // เปอร์เซ็นต์ความพึงพอใจรายด้าน (0-100)
            $table->unsignedTinyInteger('dimension_service')->default(0);  // ด้านการให้บริการ
            $table->unsignedTinyInteger('dimension_staff')->default(0);    // ด้านบุคลากร
            $table->unsignedTinyInteger('dimension_facility')->default(0); // ด้านสถานที่
            
            $table->boolean('is_published')->default(false); // สถานะการเผยแพร่หน้าบ้าน
            $table->timestamps();
            $table->softDeletes(); // ป้องกันการลบข้อมูลพลาด
        });
    }

    public function down()
    {
        Schema::dropIfExists('satisfaction_summaries');
    }
}