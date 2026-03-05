<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBcApiTracker extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bc_api_tracker', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('api_name');
            $table->string('request_method');
            $table->string('request_url');  
            $table->text('header_parameters');
            $table->longText('request_parameters');
            $table->longText('response');
            $table->string('status');
            $table->string('client_ip');
            $table->string('response_time')->nullable();
            $table->string('created_at');
            $table->string('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bc_api_tracker');
    }
}
