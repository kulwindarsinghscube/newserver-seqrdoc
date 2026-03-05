<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExcelMergeLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('excel_merge_logs', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('excel merge log id');
            $table->string('raw_excel')->nullable()->comment('raw excel');
            $table->string('processed_excel')->nullable()->comment('processed excel');
            $table->timestamp('date_time')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'))->comment('current date_time');
            $table->string('total_unique_records')->nullable()->comment('total unique records');
            $table->string('username')->nullable()->comment('username');
            $table->string('status')->nullable()->comment('status suceess or failed');
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
        Schema::dropIfExists('excel_merge_logs');
    }
}
