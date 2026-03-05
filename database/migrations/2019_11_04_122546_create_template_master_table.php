        <?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemplateMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('template_master', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('template_name');
            $table->string('actual_template_name');
            $table->string('template_desc');
            $table->integer('bg_template_id');
            $table->integer('background_template_status');
            $table->string('template_size')->nullable();
            $table->string('background_gilloche_id')->nullable();
            $table->integer('width');
            $table->integer('height');
            $table->string('unique_serial_no')->nullable();
            $table->string('lock_element');
            $table->integer('create_refresh');
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->integer('status');
            $table->integer('site_id');
            $table->boolean('is_block_chain')->default(0)->comment('0:Non Block Chain, 1:Block Chain');
            $table->string('bc_document_description')->nullable();
            $table->string('bc_document_type')->nullable();
            $table->string('bc_contract_address')->nullable();

            $table->tinyInteger('is_back_template')->default(0);
            $table->integer('back_bg_template_id')->default(0);
            $table->string('sample_pdf', 255)->nullable();
            $table->timestamp('sample_pdf_last_updated_at')->nullable();

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
        Schema::dropIfExists('template_master');
    }
}
