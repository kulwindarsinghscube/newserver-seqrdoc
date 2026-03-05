<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExceluploadhistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('excelupload_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('template_name');
            $table->string('excel_sheet_name');
            $table->string('pdf_file');
            $table->string('user');
            $table->integer('no_of_records');
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
        Schema::dropIfExists('excelupload_history');
    }
}
