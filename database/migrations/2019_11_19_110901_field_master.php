<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FieldMaster extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fields_master', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('template_id');
            $table->string('name');
            $table->string('mapped_name')->nullable();
            $table->string('security_type')->nullable();
            $table->integer('field_position');
            $table->string('text_justification');
            $table->string('x_pos');
            $table->string('y_pos');
            $table->string('width');
            $table->string('height');
            $table->string('font_style')->nullable();
            $table->integer('font_id')->nullable();
            $table->string('font_size');
            $table->string('font_color');
            $table->boolean('is_font_case')->default(1);
            $table->text('sample_text')->nullable();
            $table->string('sample_image')->nullable();
            $table->string('angle')->nullable();
            $table->string('font_color_extra')->nullable();
            $table->string('line_gap')->nullable();
            $table->string('length')->nullable();
            $table->string('uv_percentage')->nullable();
            $table->string('field_sample_text_width')->nullable();
            $table->string('field_sample_text_vertical_width')->nullable();
            $table->string('field_sample_text_horizontal_width')->nullable();
            $table->string('lock_index')->nullable();
            $table->string('is_repeat')->nullable();
            $table->string('infinite_height')->nullable();
            $table->string('include_image')->nullable();
            $table->integer('grey_scale')->nullable();
            $table->string('is_uv_image')->nullable();
            $table->string('is_transparent_image')->nullable();
            $table->string('text_opicity')->nullable();
            $table->integer('visible')->nullable();
            $table->integer('visible_varification')->nullable();
            $table->string('combo_qr_text', 2048)->nullable();
            $table->string('is_mapped')->nullable();
            $table->boolean('is_meta_data')->default(0)->comment('0: Non Meta, 1 : Metadata');
            $table->string('meta_data_label')->nullable();
            $table->string('meta_data_value')->nullable();
            $table->tinyInteger('is_encrypted_qr')->default(0);
            $table->string('encrypted_qr_text', 2048)->nullable();
            $table->bigInteger('created_by');
            $table->bigInteger('updated_by');
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
        Schema::dropIfExists('fields_master');
    }
}
