<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFontMastersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('font_master', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('font master id');
            $table->string('font_name')->nullable()->comment('font name');
            $table->string('font_filename_N')->nullable()->comment('font normal filename');
            $table->string('font_filename_B')->nullable()->comment('font bold filename'); 
            $table->string('font_filename_I')->nullable()->comment('font bold-italic filename'); 
            $table->string('font_filename_BI')->nullable()->comment('font bold-italic filename');
            $table->string('font_filename')->nullable()->comment('font filename');
            $table->integer('created_by')->nullable()->comment('created by id');
            $table->integer('updated_by')->nullable()->comment('updated by id');
            $table->tinyInteger('status')->nullable()->default(1)->comment('1 means active and 0 means deactive');
            $table->tinyInteger('publish')->nullable()->default(1)->comment('1 means active and 0 means deactive');      
            $table->softDeletes();
            $table->integer('site_id')->nullable()->comment('site id'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('font_master');
    }
}
