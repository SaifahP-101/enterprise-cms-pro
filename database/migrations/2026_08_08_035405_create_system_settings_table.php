<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSystemSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            
            // ใช้ string ความยาว 100 พร้อม unique() ซึ่งจะสร้าง Index ให้อัตโนมัติเพื่อความเร็วในการ Query
            $table->string('key', 100)->unique()->comment('ชื่อคีย์อ้างอิงของตัวแปร (เช่น google_drive_refresh_token)');
            
            // ใช้ text รองรับสายอักขระยาวๆ เช่น Token ที่ถูกเข้ารหัส (Encrypted String)
            $table->text('value')->nullable()->comment('ค่าของตัวแปร');
            
            // เพิ่มคำอธิบายเพื่อให้แอดมินเข้าใจว่าคีย์นี้ใช้ทำอะไร
            $table->string('description', 255)->nullable()->comment('คำอธิบายการใช้งานตัวแปรนี้');
            
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
        Schema::dropIfExists('system_settings');
    }
}