<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ConsumptionReport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('grade_card_data', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('roll_no');
            $table->string('result_no',128); 
            $table->string('student_name',256); 
            $table->string('registration_no',128); 
            $table->string('enrollment_no',128); 
            $table->string('term',64); 
            $table->string('examination',64); 
            $table->string('programme',64); 
            $table->string('department',64); 
            $table->string('scheme',64);
            $table->string('student_type',64);
            $table->string('section',64);
            $table->string('serial_no',256);
            $table->integer('updated_by');
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
        Schema::dropIfExists('grade_card_data');
    }
}
