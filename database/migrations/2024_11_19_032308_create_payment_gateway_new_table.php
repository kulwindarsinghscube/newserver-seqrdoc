<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentGatewayNewTable extends Migration
{
    public function up()
    {
        Schema::create('payment_gateway_new', function (Blueprint $table) {
            $table->increments('id'); // Auto-increment primary key
            $table->string('pg_name', 191)->nullable();
            $table->string('pg_title', 191)->nullable();
            $table->string('merchant_key', 191)->nullable();
            $table->string('salt', 191)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->tinyInteger('publish')->default(1);
            $table->string('test_merchant_key', 191)->nullable();
            $table->string('test_salt', 191)->nullable();
            $table->integer('site_id')->nullable();
            $table->string('website', 191)->nullable();
            $table->string('channel', 191)->nullable();
            $table->string('industry_type', 191)->nullable();
            $table->string('payment_mode', 50)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->integer('updated_by')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_gateway_new');
    }
}

