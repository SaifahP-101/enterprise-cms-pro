<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContentViewLogsTable extends Migration
{
    public function up()
    {
        Schema::create('content_view_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained('contents')->onDelete('cascade');
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['content_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('content_view_logs');
    }
}