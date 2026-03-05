<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InstituteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('institute_table', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('institute_username');
            $table->string('username');
            $table->string('password');
            $table->bigInteger('created_by');
            $table->bigInteger('updated_by');
            $table->tinyInteger('status');
            $table->tinyInteger('publish');
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
        Schema::dropIfExists('institute_table');
    }
}
