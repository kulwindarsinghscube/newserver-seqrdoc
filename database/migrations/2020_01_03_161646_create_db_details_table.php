<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDbDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('db_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('template_id')->nullable()->comment('template id');
            $table->string('db_name')->nullable()->comment('database name');
            $table->string('db_host_address')->nullable()->comment('host address');
            $table->string('username')->nullable()->comment(' db username');
            $table->string('password')->nullable()->comment('db password');
            $table->integer('port')->nullable()->comment('db port');
            $table->string('table_name')->nullable()->comment('mapped table name');
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
        Schema::dropIfExists('db_details');
    }
}
