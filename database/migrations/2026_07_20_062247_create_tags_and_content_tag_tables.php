<?php
// database/migrations/2026_07_19_000001_create_tags_and_content_tag_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTagsAndContentTagTables extends Migration
{
    public function up()
    {
        // 1. ตารางเก็บรายชื่อแท็กสากล
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique(); // รองรับการทำ URL คัดกรองบทความตามแท็ก
            $table->timestamps();
        });

        // 2. ตาราง Pivot Table เชื่อมโยงความสัมพันธ์ Many-to-Many (Content <-> Tag)
        Schema::create('content_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained('contents')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('tags')->onDelete('cascade');
            
            // ป้องกันการผูกแท็กซ้ำในบทความเดิม
            $table->unique(['content_id', 'tag_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('content_tag');
        Schema::dropIfExists('tags');
    }
}