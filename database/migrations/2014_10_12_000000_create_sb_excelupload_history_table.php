<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSbExceluploadHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sb_excelupload_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('template_name',191);
            $table->string('excel_sheet_name',191);  
            $table->string('user',191);    
            $table->integer('no_of_records');
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
        Schema::dropIfExists('sb_excelupload_history');
    }
}
