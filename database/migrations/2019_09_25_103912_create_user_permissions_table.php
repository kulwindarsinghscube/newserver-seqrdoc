<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->nullable()->comment('User Id');
            $table->integer('permission_id')->nullable()->comment('Acl permission Id');
            $table->integer('role_id')->nullable()->comment('Role Id');
            $table->string('route_name',256)->nullable()->comment('Route name');
            $table->integer('created_by')->nullable()->comment('Created by');
            $table->integer('updated_by')->nullable()->comment('Updated by');
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
        Schema::dropIfExists('user_permissions');
    }
}
