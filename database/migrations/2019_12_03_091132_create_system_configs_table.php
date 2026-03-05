<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSystemConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_config', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('system config id');
            $table->string('printer_name')->nullable()->comment('printer name');
            $table->string('print_color')->nullable()->default('RGB')->comment('print color');
            $table->string('timezone')->nullable()->comment('timezone');
            $table->string('auto_logout')->nullable()->comment('auto logout');
            $table->string('smtp')->nullable()->comment('smtp');
            $table->string('port')->nullable()->comment('port');
            $table->string('sender_email')->nullable()->comment('sender email');
            $table->integer('sandboxing')->nullable()->comment('sandboxing'); 
            $table->integer('varification_sandboxing')->nullable()->comment('varification_sandboxing');
            $table->string('password')->nullable()->comment('password');
            $table->integer('site_id')->nullable()->comment('site id'); 
            $table->enum('file_aws_local', [0, 1])->default(1); 
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
        Schema::dropIfExists('system_config');
    }
}
