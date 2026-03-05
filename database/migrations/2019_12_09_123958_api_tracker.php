<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ApiTracker extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('api_tracker', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('client_ip');
            $table->string('request_url');
            $table->string('request_method');
            $table->string('status');
            $table->text('header_parameters');
            $table->longText('request_parameters');
            $table->longText('response_parameters');
            $table->string('response_time')->nullable();
            $table->string('created');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('api_tracker');
    }
}
