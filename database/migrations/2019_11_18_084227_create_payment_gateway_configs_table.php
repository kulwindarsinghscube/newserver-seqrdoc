<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentGatewayConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_gateway_config', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('payment gateway config id');
            $table->integer('pg_id')->nullable()->comment('payment gateway id');
            $table->integer('pg_status')->nullable()->comment('payment gateway status');
            $table->double('amount')->nullable()->comment('payment gateway amount');
            $table->integer('crendential')->default(0)->nullable()->comment('payment gateway crendential');
            $table->string('updated_by')->nullable()->comment('updated by id');
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
        Schema::dropIfExists('payment_gateway_config');
    }
}
