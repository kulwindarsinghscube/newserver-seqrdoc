<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGenerationRequestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('generation_request_table', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('unique_request_id', 191)->unique()->nullable();
            $table->string('input_file_path', 255)->nullable();
            $table->integer('total_row_pages')->nullable();
            $table->integer('total_generated_doc')->nullable();
            $table->enum('generation_type', ['excel2pdf', 'pdf2pdf', 'custom'])
                  ->default('excel2pdf')
                  ->charset('utf8mb3')
                  ->collation('utf8mb3_general_ci')
                  ->notNullable();
            $table->enum('status', [
                'pending',
                'in-progress',
                'Error Occured',
                'Partial Completed',
                'Completed',
                'Cancelled',
                'Paused',
                'Error Uploading DB Records',
                'Error Uploading Verification Files',
                'Error Uploading File to server',
                'Error Uploading printable File',
            ])->nullable();
            $table->integer('template_id')->nullable();
            $table->string('procesed_pdf_file', 255)->nullable();
           
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('generation_request_table');
    }
}

