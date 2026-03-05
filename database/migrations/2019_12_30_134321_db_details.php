<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DbDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('db_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->Integer('template_id');
            $table->string('db_name');
            $table->string('db_host_address');
            $table->string('username');
            $table->string('password');
            $table->Integer('port');
            $table->String('table_name');
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
        Schema::dropIfExists('db_details');
    }
}
