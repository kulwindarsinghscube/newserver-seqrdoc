<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SuperAdmin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('super_admin_new', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('property')->nullable();
            $table->string('value')->nullable();
            $table->date('installation_date')->nullable();
            $table->string('current_value')->nullable();
            $table->integer('site_id')->nullable();
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
        Schema::dropIfExists('super_admin_new');
    }
}
