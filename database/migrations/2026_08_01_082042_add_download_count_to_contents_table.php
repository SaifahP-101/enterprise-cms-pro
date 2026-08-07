<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDownloadCountToContentsTable extends Migration
{
    public function up()
    {
        Schema::table('contents', function (Blueprint $table) {
            if (!Schema::hasColumn('contents', 'download_count')) {
                $table->unsignedBigInteger('download_count')->default(0)->after('view_count')->comment('ยอดรวมการดาวน์โหลดเอกสาร');
            }
        });
    }

    public function down()
    {
        Schema::table('contents', function (Blueprint $table) {
            if (Schema::hasColumn('contents', 'download_count')) {
                $table->dropColumn('download_count');
            }
        });
    }
}