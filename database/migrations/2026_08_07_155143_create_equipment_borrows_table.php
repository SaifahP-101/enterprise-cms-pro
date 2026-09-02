<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEquipmentBorrowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('equipment_borrows', function (Blueprint $table) {
            $table->id();
            
            // ข้อมูลผู้ยืม
            $table->string('borrower_name', 150)->index()->comment('ชื่อ-นามสกุลผู้ยืม');
            $table->string('borrower_status', 50)->index()->comment('สถานะผู้ยืม เช่น นักศึกษา, บุคลากร, บุคคลภายนอก');
            $table->string('faculty_department', 150)->index()->comment('คณะหรือหน่วยงาน');
            $table->string('phone_number', 20)->comment('เบอร์โทรศัพท์ติดต่อ');
            
            // ข้อมูลการยืม
            $table->string('equipment_name', 200)->index()->comment('รายการอุปกรณ์หรือครุภัณฑ์ที่ยืม');
            $table->integer('quantity')->unsigned()->default(1)->comment('จำนวนที่ยืม');
            $table->date('borrow_date')->index()->comment('วันที่ยืม');
            $table->date('expected_return_date')->index()->comment('วันที่กำหนดคืน');
            $table->text('purpose')->nullable()->comment('วัตถุประสงค์ในการยืม (ไม่บังคับ)');
            
            // ข้อมูลไฟล์แนบ (เก็บ Path ของไฟล์ ไม่เก็บไฟล์ดิบลง DB)
            $table->string('image_path', 255)->nullable()->comment('รูปถ่ายอุปกรณ์ (ไม่บังคับ)');
            
            // Timestamps & Soft Deletes
            $table->timestamps();
            $table->softDeletes()->index(); // ป้องกันข้อมูลสูญหาย และสร้าง Index ให้ deleted_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('equipment_borrows');
    }
}