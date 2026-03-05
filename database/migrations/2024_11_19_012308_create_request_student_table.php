<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestStudentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_student_table', function (Blueprint $table) {
            $table->bigIncrements('id'); // Automatically creates an unsigned BIGINT primary key field
            $table->string('serial_no', 191)->nullable();
            $table->string('student_name', 191)->nullable();
            $table->string('certificate_filename', 191)->nullable();
            $table->integer('template_id')->nullable();
            $table->string('key', 191)->nullable();
            $table->string('path', 191)->nullable();
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->enum('status', ['1', '0'])->default('0');
            $table->enum('publish', ['1', '0'])->default('1');
            $table->integer('scan_count')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->default(null);
            $table->integer('site_id')->nullable();
            $table->tinyInteger('template_type')->default(0)->comment('0:Template Maker, 2:Custom Template, 1: PDF2PDF');
            $table->string('bc_txn_hash', 256)->nullable();
            $table->text('bc_ipfs_hash')->nullable();
            $table->string('certificate_type', 20)->nullable()->comment('Degree, Marksheet');
            $table->text('json_data')->nullable();
            $table->tinyInteger('encryption_type')->default(0);
            $table->string('unique_request_id', 191)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('request_student_table');
    }
}
