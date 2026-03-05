<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSessionManagerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('session_manager', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->integer('user_id')->nullable()->comment('user id');
            $table->string('session_id',765)->nullable()->comment('session id');
            $table->dateTime('login_time')->nullable()->comment('login time');
            $table->dateTime('logout_time')->nullable()->comment('logout_time');
            $table->integer('is_logged')->nullable()->comment('is -logged');
            $table->string('device_type',765)->nullable()->comment('device type');
            $table->string('ip',765)->nullable()->comment('IP');
            $table->string('latitude',765)->nullable()->comment('latitude');
            $table->string('longitude',765)->nullable()->comment('longitude');
            $table->integer('site_id')->nullable()->comment('site id'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('session_manager');
    }
}
