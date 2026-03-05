<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentGatewaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_gateway', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('payment gateway id');
            $table->string('pg_name')->nullable()->comment('payment gateway name');
            $table->string('merchant_key')->nullable()->comment('merchant key');
            $table->string('salt')->nullable()->comment('salt');
            $table->tinyInteger('status')->nullable()->default(1)->comment('1 means active and 0 means deactive');
            $table->tinyInteger('publish')->nullable()->default(1)->comment('1 means active and 0 means deactive');      
            $table->string('test_merchant_key')->nullable()->comment('test merchant key');
            $table->string('test_salt')->nullable()->comment('test salt');
            $table->integer('updated_by')->nullable()->comment('updated by id');
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
        Schema::dropIfExists('payment_gateway');
    }
}
