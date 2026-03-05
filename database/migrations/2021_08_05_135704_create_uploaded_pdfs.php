<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUploadedPdfs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('uploaded_pdfs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('file_name')->nullable();
            $table->longText('extractor_details')->nullable();
            $table->longText('placer_details')->nullable();
            $table->longText('ep_details')->nullable();
            $table->string('template_title',500)->nullable();
            $table->string('template_name')->nullable();
            $table->string('pdf_page')->nullable();
            $table->integer('print_bg_file')->nullable()->default(0);
            $table->string('print_bg_status')->nullable()->default('No');
            $table->integer('verification_bg_file')->nullable()->default(0);
            $table->string('verification_bg_status')->nullable()->default('No');
            $table->integer('generated_by');
            $table->integer('is_block_chain')->default(0);
            $table->string('bc_document_description', 256)->nullable();
            $table->string('bc_document_type', 256)->nullable();
            $table->string('bc_contract_address', 256)->nullable();
            
            $table->integer('publish')->nullable()->default(1);


            
            $table->tinyInteger('map_type')->default(0);



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
        Schema::dropIfExists('uploaded_pdfs');
    }
}
