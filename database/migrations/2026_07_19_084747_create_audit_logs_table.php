<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuditLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            
            // 🔗 ผูกบัญชีผู้ใช้ที่ทำรายการ หากผู้ใช้โดนลบให้ตั้งค่าเป็น Null เพื่อรักษาประวัติศาสตร์ข้อมูลไว้ (Set Null Constraint)
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

            $table->string('action')->comment('ประเภทธุรกรรม เช่น CREATE, UPDATE, DELETE');
            $table->string('auditable_type')->comment('ชื่อโมเดลที่ถูกกระทำ เช่น App\Models\Content');
            $table->unsignedBigInteger('auditable_id')->comment('ไอดีของแถวข้อมูลที่ถูกกระทำ');
            
            // ⚡ Data Diffing Layer: จัดเก็บข้อมูลชุดเก่าและชุดใหม่ในรูปแบบ JSON Payload
            $table->json('old_values')->nullable()->comment('ค่าข้อมูลดิบก่อนการเปลี่ยนแปลง');
            $table->json('new_values')->nullable()->comment('ค่าข้อมูลชุดใหม่หลังการกดบันทึก');

            // 🛡️ Network Tracing Parameters
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            // ⚡ Optimization Indexing: เร่งความเร็วการสืบค้นประวัติตามช่วงเวลา และคัดกรองตามรายบุคคล
            $table->index(['auditable_type', 'auditable_id']);
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('audit_logs');
    }
}