<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use DB;
class CreateStudentTablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_table', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('serial_no')->nullable()->comment('');
            $table->string('student_name')->nullable()->comment('');
            $table->string('certificate_filename')->comment('');
            $table->integer('template_id')->nullable()->comment('');
            $table->string('key')->comment('');
            $table->string('path')->comment('');
            $table->integer('created_by')->comment('');
            $table->integer('updated_by')->comment('');
            $table->enum('status',[1,0])->default(0)->comment('');
            $table->enum('publish',[1,0])->default(1)->comment('');
            $table->integer('scan_count')->nullable()->defalut(0)->comment('');
            $table->integer('site_id')->nullable()->comment('site id');
            $table->boolean('template_type')->default(0)->comment('0:Template Maker, 1: PDF2PDF');
            $table->boolean('is_block_chain')->default(0)->comment('0:Non Block Chain, 1:Block Chain');
            $table->string('bc_document_description')->nullable();
            $table->string('bc_document_type')->nullable();
            $table->string('bc_txn_hash')->nullable();
            $table->text('bc_ipfs_hash')->nullable();
            $table->text('pinata_ipfs_hash')->nullable();
            $table->string('certificate_type', 20)->nullable();
            $table->text('json_data')->nullable();
            $table->tinyInteger('encryption_type')->default(0);
            $table->tinyInteger('files_storage')->default(0);
            $table->text('bc_file_hash')->nullable();

            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
            $table->tinyInteger('bc_sc_id')->default(0);
            //$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_table');
    }
}
