<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSbTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sb_transactions', function (Blueprint $table) {
            $table->bigIncrements('sr_no');
            $table->bigInteger('pay_gateway_id'); 
            $table->string('trans_id_ref',191);
            $table->string('trans_id_gateway',191);
            $table->string('payment_mode',191);
            $table->double('amount');
            $table->double('additional');
            $table->integer('user_id'); 
            $table->string('student_key',191);
            $table->tinyInteger('trans_status');
            $table->tinyInteger('publish');
            $table->timestamps();
            $table->integer('site_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sb_transactions');
    }
}
