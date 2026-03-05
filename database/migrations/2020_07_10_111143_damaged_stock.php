<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DamagedStock extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('damaged_stationary', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('card_category',32);
            $table->string('serial_no',256); 
            $table->string('type',32);  
            $table->string('remark',512);  
            $table->integer('exam'); 
            $table->integer('degree'); 
            $table->integer('semester');     
            $table->string('registration_no',20);  
            $table->integer('branch');
            $table->integer('added_by'); 
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
        Schema::dropIfExists('damaged_stationary');
    }
}
