<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestPrintingDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_printing_details', function (Blueprint $table) {
            $table->bigIncrements('id'); // Automatically creates an unsigned BIGINT primary key field
            $table->integer('student_table_id')->nullable();
            $table->string('username', 191)->nullable()->comment('username');
            $table->dateTime('print_datetime')->nullable()->comment('print datetime');
            $table->string('printer_name', 191)->nullable()->comment('printer name');
            $table->integer('print_count')->nullable()->comment('print count');
            $table->string('print_serial_no', 191)->nullable()->comment('print serial no');
            $table->string('sr_no', 191)->nullable()->comment('sr no');
            $table->string('template_name', 191)->nullable()->comment('template name');
            $table->integer('card_serial_no')->nullable();
            $table->integer('scan_count')->default(0)->comment('scan count');
            $table->integer('created_by')->nullable()->comment('created by id');
            $table->integer('updated_by')->nullable()->comment('updated by id');
            $table->tinyInteger('reprint')->default(0)->comment('reprint');
            $table->tinyInteger('status')->default(1)->comment('1 means active and 0 means deactive');
            $table->tinyInteger('publish')->default(1)->comment('1 means publish and 0 means depublish');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->default(null);
            $table->integer('site_id')->nullable();
            $table->string('unique_request_id', 191)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('request_printing_details');
    }
}
