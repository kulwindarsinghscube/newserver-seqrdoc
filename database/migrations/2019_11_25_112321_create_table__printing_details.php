<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTablePrintingDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table__printing_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('username');
            $table->date('print_datetime');
            $table->string('printer_name');
            $table->integer('print_count');
            $table->string('print_serial_no');
            $table->string('sr_no');
            $table->string('template_name');
            $table->integer('scan_count')->nullable();
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->integer('reprint');
            $table->integer('status');
            $table->integer('publish');
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
        Schema::dropIfExists('table__printing_details');
    }
}
