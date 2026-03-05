<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIndividualRecords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('individual_records', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('file_name')->nullable();
            $table->integer('file_records_id')->nullable();
            $table->integer('template_id')->nullable();
            $table->string('template_name')->nullable();
            $table->string('pdf_page')->nullable();
            $table->integer('page_no')->nullable();
            $table->string('encoded_id')->nullable();
            $table->string('unique_no')->nullable();
            $table->string('qr_details')->nullable();
            $table->integer('userid')->nullable();
            $table->string('record_unique_id')->nullable();
            $table->integer('publish')->nullable()->default(1);
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
        Schema::dropIfExists('individual_records');
    }
}
