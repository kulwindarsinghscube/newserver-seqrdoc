<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAclPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acl_permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('route_name',255)->nullable()->comment('Route name');
            $table->text('description')->nullable()->comment('Route description');
            $table->text('description_alias')->nullable()->comment('Route description');
            $table->string('method_name',10)->nullable()->comment('Route method');
            $table->text('action_name')->nullable()->comment('Route action');
            $table->string('main_module',50)->nullable()->comment('Main module name');
            $table->string('sub_module',50)->nullable()->comment('Sub module name');
            $table->string('module',50)->nullable()->comment('Module name');
            $table->string('route_method',50)->nullable()->comment('Controller method name');
            $table->integer('order')->nullable()->comment('route order');
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
        Schema::dropIfExists('acl_permissions');
    }
}
