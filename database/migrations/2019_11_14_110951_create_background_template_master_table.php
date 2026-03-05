<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBackgroundTemplateMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('background_template_master', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('background_name');
            $table->string('image_path');
            $table->string('width');
            $table->string('height');
            $table->enum('status',[0,1])->default(0);
            $table->string('background_opicity')->nullable();
            $table->integer('created_by');
            $table->integer('updated_by');
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
        Schema::dropIfExists('background_template_master');
    }
}
