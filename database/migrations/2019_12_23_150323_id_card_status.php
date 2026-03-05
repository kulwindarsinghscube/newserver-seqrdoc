<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class IdCardStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('id_card_status', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('template_name');
            $table->string('excel_sheet');
            $table->string('request_number');
            $table->string('status');
            $table->Integer('rows');
            $table->timestamp('created_on')->nullable();
            $table->integer('site_id');
            $table->timestamp('updated_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('id_card_status');
    }
}
