<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFileRecords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('file_records', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('template_id')->nullable();
            $table->string('template_name')->nullable();
            $table->string('pdf_page')->nullable();
            $table->integer('total_records')->nullable();
            $table->integer('pages_in_pdf')->nullable();
            $table->text('source_file')->nullable();
            $table->integer('userid')->nullable();
            $table->string('record_unique_id')->nullable();
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
        Schema::dropIfExists('file_records');
    }
}
