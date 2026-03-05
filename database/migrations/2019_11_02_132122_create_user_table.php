<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_table', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('fullname');
            $table->string('username');
            $table->string('password');
            $table->string('email_id')->unique();
            $table->string('mobile_no');
            $table->string('status');
            $table->string('verify_by')->nullable();
            $table->integer('is_verified')->nullable();
            $table->enum('publish',[1,0])->default(1);
            $table->integer('is_admin')->default(0);
            $table->string('token')->nullable();
            $table->integer('OTP')->default(0);
            $table->string('device_type')->default('web');
            $table->integer('print_limit')->default(0);
            $table->integer('role_id')->default(0);
            $table->tinyInteger('logout')->default(0);
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
        Schema::dropIfExists('user_table');
    }
}
