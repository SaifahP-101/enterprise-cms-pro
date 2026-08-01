<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCalendarEventsTable extends Migration
{
    public function up()
    {
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');                           // ชื่อกิจกรรม
            $table->text('description')->nullable();           // รายละเอียด
            $table->date('event_date')->index();               // วันที่จัดกิจกรรม (ทำดัชนีเพื่อดึงข้อมูลเร็ว)
            $table->time('start_time')->nullable();            // เวลาเริ่ม
            $table->time('end_time')->nullable();              // เวลาสิ้นสุด
            $table->string('location')->nullable();            // สถานที่
            $table->boolean('is_active')->default(true)->index(); 
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('calendar_events');
    }
}