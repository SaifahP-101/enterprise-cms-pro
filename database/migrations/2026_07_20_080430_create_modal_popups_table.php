<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModalPopupsTable extends Migration
{
    public function up()
    {
        Schema::create('modal_popups', function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment('ชื่อแคมเปญหรือประกาศ');
            $table->string('image_path')->comment('รูปภาพป๊อปอัป');
            $table->string('link_url')->nullable();
            $table->boolean('is_active')->default(false)->comment('สถานะเปิดใช้งานหลัก');
            $table->dateTime('start_date')->nullable()->comment('เวลาเริ่มแสดงผล (ถ้ามี)');
            $table->dateTime('end_date')->nullable()->comment('เวลาสิ้นสุด/หมดอายุอัตโนมัติ (ถ้ามี)');
            $table->timestamps();

            // ⚡ ทำดัชนีเพื่อเร่งสปีดคิวรีตรวจสอบเวลาหมดอายุ
            $table->index(['is_active', 'start_date', 'end_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('modal_popups');
    }
}