<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSbScannedHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sb_scanned_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->datetime('date_time');
            $table->string('device_type',191);  
            $table->text('scanned_data');
            $table->string('scan_by',191);
            $table->integer('scan_result'); 
            $table->timestamps();
            $table->integer('site_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sb_scanned_history');
    }
}
