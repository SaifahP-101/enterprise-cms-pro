<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContentDownloadLogsTable extends Migration
{
    public function up()
    {
        Schema::create('content_download_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained('contents')->onDelete('cascade');
            $table->string('fullname')->comment('ชื่อ-นามสกุล ผู้ขอรับเอกสาร');
            $table->string('email')->nullable()->comment('อีเมลติดต่อ');
            $table->string('phone', 30)->nullable()->comment('เบอร์โทรศัพท์');
            $table->string('organization')->nullable()->comment('หน่วยงาน/สถาบัน/คณะ');
            $table->text('purpose')->nullable()->comment('วัตถุประสงค์การนำไปใช้ประโยชน์');
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['content_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('content_download_logs');
    }
}