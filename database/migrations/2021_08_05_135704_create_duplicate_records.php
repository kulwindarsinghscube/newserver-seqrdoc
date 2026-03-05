<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDuplicateRecords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('duplicate_records', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->longText('filename')->nullable();
            $table->integer('template_id')->nullable();
            $table->string('pdf_page')->nullable();
            $table->longText('unids')->nullable();
            $table->integer('total_count')->nullable();
            $table->integer('user_id')->nullable();
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
        Schema::dropIfExists('duplicate_records');
    }
}
