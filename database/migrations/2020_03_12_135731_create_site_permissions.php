<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSitePermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('site_permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('site_id')->nullable();
            $table->integer('permission_id')->nullable();
            $table->string('route_name')->nullable();
            $table->string('main_module')->nullable();
            $table->string('sub_module')->nullable();
            $table->text('description')->nullable();
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
        Schema::dropIfExists('site_permissions');
    }
}
