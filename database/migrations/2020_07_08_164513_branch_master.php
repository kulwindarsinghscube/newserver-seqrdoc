<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BranchMaster extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('branch_master', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('branch_name_long',256);
            $table->string('branch_name_short',32);  
            $table->integer('degree_id'); 
            $table->integer('is_active');      
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
        Schema::dropIfExists('branch_master');
    }
}
