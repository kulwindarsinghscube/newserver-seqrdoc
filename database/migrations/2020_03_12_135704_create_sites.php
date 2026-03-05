<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSites extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->bigIncrements('site_id');
            $table->string('site_url')->nullable();
            $table->string('status')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('apple_app_url')->nullable();
            $table->string('android_app_url')->nullable();
            $table->string('license_key')->nullable();
            $table->string('pdf_storage_path')->nullable();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('organization_category')->nullable();
            $table->string('bc_wallet_address')->nullable();
            $table->string('bc_private_key')->nullable();
            $table->string('issuer_did')->nullable();
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
        Schema::dropIfExists('sites');
    }
}
