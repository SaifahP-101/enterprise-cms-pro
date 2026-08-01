<?php
// database/migrations/xxxx_xx_xx_xxxxxx_add_is_admin_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsAdminToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            /**
             * การเพิ่มฟิลด์ Flag เพื่อแยกแยะแอดมินออกจากผู้ใช้งานทั่วไป
             * มาตรฐานความปลอดภัย: ต้องกำหนด default เป็น false (0) เสมอ 
             * เพื่อป้องกันกรณีระบบผิดพลาดแล้วเปิดสิทธิ์แอดมินให้ผู้ใช้ใหม่โดยไม่เจตนา
             */
            $table->boolean('is_admin')->default(false)->after('email');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_admin');
        });
    }
}