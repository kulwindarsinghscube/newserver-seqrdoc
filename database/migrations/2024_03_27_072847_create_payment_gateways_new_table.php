<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentGatewaysNewTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_gateway_new', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('payment gateway id');
            $table->string('pg_name')->nullable()->comment('payment gateway name');
            $table->string('pg_title')->nullable()->comment('payment gateway title');
            $table->string('merchant_key')->nullable()->comment('merchant key');
            $table->string('salt')->nullable()->comment('salt');
            $table->tinyInteger('status')->nullable()->default(1)->comment('1 means active and 0 means deactive');
            $table->tinyInteger('publish')->nullable()->default(1)->comment('1 means active and 0 means deactive');      
            $table->string('test_merchant_key')->nullable()->comment('test merchant key');
            $table->string('test_salt')->nullable()->comment('test salt');
            $table->string('website')->nullable()->comment('website');
            $table->string('channel')->nullable()->comment('channel');
            $table->string('industry_type')->nullable()->comment('industry type');
            $table->string('payment_mode')->nullable()->comment('payment mode');
            $table->integer('created_by')->nullable()->comment('created by id');
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
        Schema::dropIfExists('payment_gateway_new');
    }
}
