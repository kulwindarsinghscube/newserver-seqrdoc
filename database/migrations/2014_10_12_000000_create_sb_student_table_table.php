<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSbStudentTableTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sb_student_table', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('serial_no',191);
            $table->string('student_name',191)->nullable();
            $table->string('certificate_filename',191);
            $table->integer('template_id');
            $table->string('key',191);
            $table->string('path',191);
            $table->integer('created_by'); 
            $table->integer('updated_by');
            $table->enum('status',[1,0])->default(0)->comment('');
            $table->enum('publish',[1,0])->default(1)->comment('');
            $table->integer('scan_count')->nullable();
            $table->timestamps();
            $table->integer('site_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sb_student_table');
    }
}
