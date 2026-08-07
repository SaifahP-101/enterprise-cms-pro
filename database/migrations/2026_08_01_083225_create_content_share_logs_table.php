<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContentShareLogsTable extends Migration
{
    public function up()
    {
        Schema::create('content_share_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained('contents')->onDelete('cascade');
            $table->string('platform', 30)->default('facebook')->comment('facebook, line, copy_link');
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['content_id', 'created_at', 'platform']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('content_share_logs');
    }
}