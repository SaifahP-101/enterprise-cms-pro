<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRbacTables extends Migration
{
    public function up()
    {
        // 1. ตารางกลุ่มบทบาท (Roles)
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('ชื่อบทบาท เช่น Super Admin, Content Editor');
            $table->string('slug')->unique()->comment('รหัสอ้างอิง เช่น super_admin, content_editor');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. ตารางรายการสิทธิ์การใช้งาน (Permissions)
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('ชื่อสิทธิ์ เช่น จัดการบทความ, ลบข้อมูล');
            $table->string('slug')->unique()->comment('รหัสอ้างอิง เช่น manage_contents, delete_contents');
            $table->string('module', 50)->comment('กลุ่มโมดูล เช่น contents, categories, menus');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('module');
        });

        // 3. ตารางผูกความสัมพันธ์ Role กับ Permission (Pivot Table)
        Schema::create('permission_role', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->foreignId('permission_id')->constrained('permissions')->onDelete('cascade');
            $table->primary(['role_id', 'permission_id']);
        });

        // 4. ตารางผูกความสัมพันธ์ User กับ Role (Pivot Table)
        Schema::create('role_user', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->primary(['user_id', 'role_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
}