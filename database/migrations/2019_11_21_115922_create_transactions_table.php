<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->bigIncrements('sr_no')->comment('auto increment id');
            $table->bigInteger('pay_gateway_id')->nullable()->comment('payment gateway id');
            $table->string('trans_id_ref')->nullable()->comment('transactions referance id');
            $table->string('trans_id_gateway')->nullable()->comment('transactions gateway id');
            $table->string('payment_mode')->nullable()->comment('payment mode');
            $table->double('amount')->nullable()->comment('payment amount');
            $table->double('additional')->nullable()->comment('additional payment data');
            $table->integer('user_id')->nullable()->comment('user id');
            $table->string('student_key')->nullable()->comment('key of student');
            $table->tinyInteger('trans_status')->nullable()->comment('payment transactions status');
            $table->tinyInteger('publish')->nullable()->default(1)->comment('payment publish');
            $table->integer('scan_id')->nullable()->comment('scan id'); 
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
        Schema::dropIfExists('transactions');
    }
}
