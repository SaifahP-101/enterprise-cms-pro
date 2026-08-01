<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePagesTable extends Migration
{
    public function up()
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment('หัวข้อหน้าเพจ');
            $table->string('slug')->unique()->comment('URL สำหรับเข้าถึงหน้าเพจ เช่น about-us');
            $table->longText('body')->comment('เนื้อหาละเอียดในหน้าเพจ');
            $table->string('secure_pdf_path')->nullable()->comment('พาร์ทเอกสารสิทธิ์ลับโชว์ในเพจ');
            $table->unsignedBigInteger('view_count')->default(0)->comment('ยอดเข้าชมสะสม');
            $table->boolean('is_active')->default(true)->comment('สถานะการเผยแพร่');
            
            // 📈 แผง SEO Optimization
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            
            $table->softDeletes(); // 🔄 รองรับการลบแบบ Soft Deletes
            $table->timestamps();

            // ⚡ ทำ Index เพื่อเร่งความเร็วในการสืบค้นด้วย Slug จากหน้าบ้าน
            $table->index(['slug', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('pages');
    }
}