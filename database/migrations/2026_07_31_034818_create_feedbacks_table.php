<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeedbacksTable extends Migration
{
    public function up()
    {
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_no', 30)->unique()->index(); // รหัสติดตามเรื่อง เช่น FB-20260730-0001
            $table->string('type', 30)->default('GENERAL')->index(); // COMPLAINT, SUGGESTION, FEEDBACK, GENERAL
            $table->string('subject');                           // หัวข้อเรื่อง
            $table->string('fullname');                          // ชื่อ-นามสกุลผู้ส่ง
            $table->string('email')->nullable();                 // อีเมลติดต่อ
            $table->string('phone', 30)->nullable();             // เบอร์โทรศัพท์
            $table->longText('message');                         // รายละเอียดข้อความ
            $table->string('status', 30)->default('PENDING')->index(); // PENDING, PROCESSING, RESOLVED, REJECTED
            $table->text('admin_note')->nullable();              // บันทึกการดำเนินการของแอดมิน
            $table->ipAddress('ip_address')->nullable();         // IP บันทึกความปลอดภัย
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('feedbacks');
    }
}