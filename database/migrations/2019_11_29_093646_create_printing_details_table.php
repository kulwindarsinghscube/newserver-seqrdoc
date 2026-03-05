<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrintingDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('printing_details', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('printing detail id');
            $table->integer('student_table_id')->nullable()->comment('student table id');
            $table->string('username')->comment('username');
            $table->dateTime('print_datetime')->comment('print datetime');
            $table->string('printer_name')->comment('printer name');
            $table->integer('print_count')->comment('print count');
            $table->string('print_serial_no')->comment('print serial no');
            $table->string('sr_no')->comment('sr no');
            $table->string('template_name')->nullable()->comment('template name');
            $table->integer('scan_count')->nullable()->comment('scan count');
            $table->integer('created_by')->comment('created by id');
            $table->integer('updated_by')->comment('updated by id');
            $table->tinyInteger('reprint')->nullable()->default(0)->comment('reprint');
            $table->tinyInteger('status')->default(1)->comment('1 means active and 0 means deactive');
            $table->tinyInteger('publish')->default(1)->comment('1 means publish and 0 means depublish');
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
        Schema::dropIfExists('printing_details');
    }
}
