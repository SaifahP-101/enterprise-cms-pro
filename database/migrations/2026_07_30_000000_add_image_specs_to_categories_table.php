<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImageSpecsToCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('image_size')->nullable()->default('2MB')->after('slug');
            $table->string('image_dimension')->nullable()->default('1200 x 630 Pixels')->after('image_size');
            $table->string('image_type')->nullable()->default('.JPG .png')->after('image_dimension');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['image_size', 'image_dimension', 'image_type']);
        });
    }
}
