<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScannedHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scanned_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->datetime('date_time')->nullable()->comment('date time of scane');
            $table->string('device_type')->nullable()->comment('device type of scan');
            $table->text('scanned_data')->nullable()->comment('key of scanning');
            $table->string('scan_by')->nullable()->comment('user id of scan');
            $table->string('scan_result')->nullable()->comment('result of scanning');
            $table->integer('site_id')->nullable()->comment('site id'); 
            $table->string('document_id')->nullable()->comment('student table document id');
            $table->integer('document_status')->nullable()->comment('student table document status'); 
            $table->tinyInteger('user_type')->default(0)->comment('0: Student 1: institute'); 
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
        Schema::dropIfExists('scanned_history');
    }
}
