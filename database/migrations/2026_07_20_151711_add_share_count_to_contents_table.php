<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShareCountToContentsTable extends Migration
{
    public function up()
    {
        Schema::table('contents', function (Blueprint $table) {
            // เพิ่มคอลัมน์เก็บสถิติยอดแชร์เนื้อหา
            $table->unsignedBigInteger('share_count')->default(0)->after('view_count');
        });
    }

    public function down()
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->dropColumn('share_count');
        });
    }
}