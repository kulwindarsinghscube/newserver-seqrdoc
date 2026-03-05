<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSbPrintingDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sb_printing_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('username',191);
            $table->datetime('print_datetime');
            $table->string('printer_name',191);  
            $table->integer('print_count');
            $table->string('print_serial_no',191); 
            $table->string('sr_no',191);    
            $table->string('template_name',191); 
            $table->integer('scan_count')->nullable();
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->integer('reprint')->nullable();   
            $table->integer('status');   
            $table->integer('publish')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));      
            //$table->timestamps();
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
        Schema::dropIfExists('sb_printing_details');
    }
}
