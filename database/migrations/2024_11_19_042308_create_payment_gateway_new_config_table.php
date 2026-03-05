<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentGatewayNewConfigTable extends Migration
{
    public function up()
    {
        Schema::create('payment_gateway_new_config', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('payment gateway config id'); // Auto-incrementing big integer as primary key
            $table->integer('pg_id')->nullable()->comment('payment gateway id');
            $table->integer('pg_status')->nullable()->comment('payment gateway status');
            $table->double('amount')->nullable()->comment('payment gateway amount');
            $table->integer('crendential')->default(0)->comment('payment gateway credential');
            $table->string('updated_by', 191)->nullable()->comment('updated by id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_gateway_new_config');
    }
}
