<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class StationaryStock extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stationary_stock', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('card_category',32);
            $table->string('academic_year',64);  
            $table->date('date_of_received'); 
            $table->string('serial_no_from',256); 
            $table->string('serial_no_to',256); 
            $table->integer('quantity'); 
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
        Schema::dropIfExists('stationary_stock');
    }
}
