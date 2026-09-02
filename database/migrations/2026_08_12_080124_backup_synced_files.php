<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ⚡ เปลี่ยนจาก class CreateBackupSyncedFilesTable extends Migration เป็น Anonymous Class
return new class extends Migration
{
    public function up()
    {
        Schema::create('backup_synced_files', function (Blueprint $table) {
            $table->id();
            $table->string('file_path')->unique()->comment('พาธไฟล์บน Local');
            $table->string('file_hash')->comment('MD5 Hash ของไฟล์ตอนที่อัปโหลด');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('backup_synced_files');
    }
};